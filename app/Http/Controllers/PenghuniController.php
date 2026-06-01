<?php

namespace App\Http\Controllers;

use App\Models\LaporanPerawatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenghuniController extends Controller
{
    public function home(): View
    {
        $penghuni = $this->penghuni();

        return view('tenant.home', [
            'penghuni' => $penghuni,
            'laporanAktif' => $this->queryLaporan()->whereIn('status', ['Pending', 'Diproses'])->latest()->get(),
            'laporanSelesai' => $this->queryLaporan()->where('status', 'Selesai')->latest()->take(3)->get(),
        ]);
    }

    public function reports(): View
    {
        return view('tenant.reports', [
            'penghuni' => $this->penghuni(),
            'laporanAktif' => $this->queryLaporan()->whereIn('status', ['Pending', 'Diproses'])->latest()->get(),
        ]);
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $penghuni = $this->penghuni();
        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:100'],
            'masalah' => ['required', 'string', 'max:255'],
        ]);

        LaporanPerawatan::query()->create([
            'kos_id' => $penghuni->kos_id,
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'masalah' => $validated['masalah'],
            'kategori' => $validated['kategori'],
            'status' => 'Pending',
        ]);

        return back()->with('status', 'Laporan berhasil dibuat.');
    }

    public function history(): View
    {
        return view('tenant.history', [
            'penghuni' => $this->penghuni(),
            'laporan' => $this->queryLaporan()->where('status', 'Selesai')->latest()->get(),
        ]);
    }

    public function beriRating(Request $request, LaporanPerawatan $laporan): RedirectResponse
    {
        $this->penghuni();
        abort_unless($laporan->penghuni_id === auth()->id(), 403);
        abort_unless($laporan->status === 'Selesai', 422);

        $validated = $request->validate([
            'nilai_rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $laporan->update(['nilai_rating' => $validated['nilai_rating']]);

        return back()->with('status', 'Rating berhasil dikirim.');
    }

    private function penghuni()
    {
        abort_unless(auth()->user()?->peran === 'penghuni', 403);

        return auth()->user()->load(['kos', 'kamar']);
    }

    private function queryLaporan()
    {
        return LaporanPerawatan::query()
            ->with(['kamar', 'kos'])
            ->where('penghuni_id', auth()->id());
    }
}
