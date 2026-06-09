<?php

namespace App\Http\Controllers;

use App\Models\LaporanPerawatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    private array $statusOptions = ['Semua Status', 'Pending', 'Diproses', 'Selesai'];

    private array $kategoriOptions = ['Semua Kategori', 'Listrik', 'Air', 'AC', 'Pintu', 'Lainnya'];

    public function index(Request $request): View
    {
        $kos = $this->kosPemilik();
        $selectedStatus = $this->normalizeFilter($request->string('status')->toString(), 'Semua Status');
        $selectedKategori = $this->normalizeFilter($request->string('kategori')->toString(), 'Semua Kategori');

        $query = LaporanPerawatan::query()
            ->with(['penghuni', 'kamar'])
            ->where('kos_id', $kos->id);

        if ($selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        if ($selectedKategori !== '') {
            $query->where('kategori', $selectedKategori);
        }

        return view('owner.laporan', [
            'kos' => $kos,
            'laporan' => $query->latest()->get(),
            'statusOptions' => $this->statusOptions,
            'kategoriOptions' => $this->kategoriOptions,
            'selectedStatus' => $selectedStatus,
            'selectedKategori' => $selectedKategori,
        ]);
    }

    public function update(Request $request, LaporanPerawatan $laporan): RedirectResponse
    {
        $kos = $this->kosPemilik();
        abort_unless($laporan->kos_id === $kos->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:Pending,Diproses,Selesai'],
            'assign' => ['nullable', 'string', 'max:100'],
            'estimasi' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validated['status'] === 'Selesai' && ! $laporan->selesai_pada) {
            $validated['selesai_pada'] = now();
        }

        $laporan->update($validated);

        return back()->with('status', 'Laporan berhasil diperbarui.');
    }

    private function kosPemilik()
    {
        abort_unless(auth()->user()?->peran === 'pemilik', 403);

        return auth()->user()->kosMilik()->firstOrFail();
    }

    private function normalizeFilter(string $value, string $allLabel): string
    {
        return in_array($value, ['', $allLabel], true) ? '' : $value;
    }
}
