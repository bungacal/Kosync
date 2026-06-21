<?php

namespace App\Http\Controllers;

use App\Models\LaporanPerawatan;
use App\Models\NotifikasiPenghuni;
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

        $oldStatus = $laporan->status;

        if ($validated['status'] === 'Selesai' && ! $laporan->selesai_pada) {
            $validated['selesai_pada'] = now();
        }

        $laporan->update($validated);

        if ($oldStatus !== $laporan->status) {
            $this->notifyStatusChange($laporan->fresh(['kamar']));
        }

        return back()->with('status', 'Laporan berhasil diperbarui.');
    }

    private function notifyStatusChange(LaporanPerawatan $laporan): void
    {
        $payload = match ($laporan->status) {
            'Diproses' => [
                'tipe' => 'laporan_diproses',
                'ikon' => 'wrench',
                'warna' => 'green',
                'judul' => 'Laporan '.$laporan->kategori.' kamar '.$laporan->kamar?->nomor.' sedang diproses',
                'isi' => $laporan->estimasi ? 'Estimasi selesai: '.$laporan->estimasi : 'Tim kos sedang menangani laporan Anda.',
                'url' => route('penghuni.reports'),
            ],
            'Selesai' => [
                'tipe' => 'laporan_selesai',
                'ikon' => 'check',
                'warna' => 'amber',
                'judul' => 'Laporan '.$laporan->masalah.' telah selesai',
                'isi' => 'Silakan beri rating atas perbaikan yang telah dilakukan.',
                'url' => route('penghuni.history', ['rating' => $laporan->id]),
            ],
            default => null,
        };

        if (! $payload) {
            return;
        }

        NotifikasiPenghuni::query()->updateOrCreate(
            [
                'user_id' => $laporan->penghuni_id,
                'laporan_perawatan_id' => $laporan->id,
                'tipe' => $payload['tipe'],
            ],
            [
                'ikon' => $payload['ikon'],
                'warna' => $payload['warna'],
                'judul' => $payload['judul'],
                'isi' => $payload['isi'],
                'url' => $payload['url'],
                'read_at' => null,
            ]
        );
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
