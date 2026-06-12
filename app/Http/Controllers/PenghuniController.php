<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\LaporanPerawatan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenghuniController extends Controller
{
    private const ACTIVE_STATUSES = ['Pending', 'Diproses'];
    private const COMPLETED_STATUS = 'Selesai';
    private const REPORT_CATEGORIES = ['Listrik', 'Air', 'AC', 'Pintu', 'Lainnya'];

    public function home(): View
    {
        $penghuni = $this->penghuni();

        return view('tenant.home', [
            'penghuni' => $penghuni,
            'laporanAktif' => $this->queryLaporan($penghuni)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->latest()
                ->get(),
            'laporanSelesai' => $this->queryLaporan($penghuni)
                ->where('status', self::COMPLETED_STATUS)
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }

    public function reports(): View
    {
        $penghuni = $this->penghuni();

        return view('tenant.reports', [
            'penghuni' => $penghuni,
            'laporanAktif' => $this->queryLaporan($penghuni)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->latest()
                ->get(),
        ]);
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $penghuni = $this->penghuni();
        $this->ensurePenghuniHasRoom($penghuni);

        $validated = $request->validate([
            'kategori' => ['required', 'string', Rule::in(self::REPORT_CATEGORIES)],
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

        return redirect()
            ->route('penghuni.reports')
            ->with('status', 'Laporan berhasil dibuat.');
    }

    public function history(): View
    {
        $penghuni = $this->penghuni();

        return view('tenant.history', [
            'penghuni' => $penghuni,
            'laporan' => $this->queryLaporan($penghuni)
                ->where('status', self::COMPLETED_STATUS)
                ->latest()
                ->get(),
        ]);
    }

    public function messages(): View
    {
        $penghuni = $this->penghuni();

        return view('tenant.messages', [
            'penghuni' => $penghuni,
            'messages' => Pesan::query()
                ->where('kos_id', $penghuni->kos_id)
                ->where('penghuni_id', $penghuni->id)
                ->oldest()
                ->get(),
            'broadcasts' => Broadcast::query()
                ->where('kos_id', $penghuni->kos_id)
                ->whereIn('target', ['Semua Penghuni', $penghuni->kamar?->lantai])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $penghuni = $this->penghuni();
        $this->ensurePenghuniHasRoom($penghuni);

        $validated = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        Pesan::query()->create([
            'kos_id' => $penghuni->kos_id,
            'penghuni_id' => $penghuni->id,
            'pengirim' => 'penghuni',
            'pesan' => $validated['pesan'],
        ]);

        return back();
    }

    public function beriRating(Request $request, LaporanPerawatan $laporan): RedirectResponse
    {
        $penghuni = $this->penghuni();

        abort_unless(
            $laporan->penghuni_id === $penghuni->id && $laporan->kos_id === $penghuni->kos_id,
            403
        );

        abort_unless($laporan->status === self::COMPLETED_STATUS, 422);

        if ($laporan->nilai_rating !== null) {
            throw ValidationException::withMessages([
                'nilai_rating' => 'Rating sudah pernah dikirim.',
            ]);
        }

        $validated = $request->validate([
            'nilai_rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $laporan->update(['nilai_rating' => $validated['nilai_rating']]);

        return redirect()
            ->route('penghuni.history')
            ->with('status', 'Rating berhasil dikirim.');
    }

    private function penghuni(): User
    {
        abort_unless(Auth::user()?->peran === 'penghuni', 403);

        return Auth::user()->loadMissing(['kos', 'kamar']);
    }

    private function queryLaporan(User $penghuni)
    {
        return LaporanPerawatan::query()
            ->with(['kamar', 'kos'])
            ->where('kos_id', $penghuni->kos_id)
            ->where('penghuni_id', $penghuni->id);
    }

    private function ensurePenghuniHasRoom(User $penghuni): void
    {
        if (! $penghuni->kos_id || ! $penghuni->kamar_id) {
            throw ValidationException::withMessages([
                'kamar_id' => 'Data kos atau kamar belum lengkap. Hubungi pemilik kos.',
            ]);
        }
    }
}
