<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KomunikasiController extends Controller
{
    private array $targetOptions = ['Semua Penghuni', 'Lantai 1', 'Lantai 2'];

    public function index(): View
    {
        $kos = $this->kosPemilik();
        $chats = $this->penghuniKos($kos->id)->get()->map(function (User $penghuni) use ($kos) {
            $last = Pesan::query()
                ->where('kos_id', $kos->id)
                ->where('penghuni_id', $penghuni->id)
                ->latest()
                ->first();

            $penghuni->last_message = $last?->pesan ?? 'Belum ada pesan.';
            $penghuni->last_time = $last?->created_at?->format('H.i') ?? '-';

            return $penghuni;
        });

        return view('owner.komunikasi', [
            'kos' => $kos,
            'chats' => $chats,
            'riwayatBroadcast' => Broadcast::query()->where('kos_id', $kos->id)->latest()->take(10)->get(),
            'targetOptions' => $this->targetOptions,
            'selectedTarget' => 'Semua Penghuni',
        ]);
    }

    public function show(User $penghuni): View
    {
        $kos = $this->kosPemilik();
        $this->abortUnlessPenghuniKos($penghuni, $kos->id);

        return view('owner.chat', [
            'kos' => $kos,
            'penghuni' => $penghuni->load('kamar'),
            'messages' => Pesan::query()
                ->where('kos_id', $kos->id)
                ->where('penghuni_id', $penghuni->id)
                ->oldest()
                ->get(),
        ]);
    }

    public function send(Request $request, User $penghuni): RedirectResponse
    {
        $kos = $this->kosPemilik();
        $this->abortUnlessPenghuniKos($penghuni, $kos->id);

        $validated = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        Pesan::query()->create([
            'kos_id' => $kos->id,
            'penghuni_id' => $penghuni->id,
            'pengirim' => 'owner',
            'pesan' => $validated['pesan'],
        ]);

        return back();
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $kos = $this->kosPemilik();

        $validated = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
            'target' => ['required', 'string', 'max:100'],
        ]);

        Broadcast::query()->create([
            'kos_id' => $kos->id,
            'pesan' => $validated['pesan'],
            'target' => $validated['target'],
            'tanggal' => now()->translatedFormat('d F'),
        ]);

        return back()->with('status', 'Broadcast berhasil dikirim.');
    }

    private function kosPemilik()
    {
        abort_unless(auth()->user()?->peran === 'pemilik', 403);

        return auth()->user()->kosMilik()->firstOrFail();
    }

    private function penghuniKos(int $kosId)
    {
        return User::query()
            ->where('peran', 'penghuni')
            ->where('kos_id', $kosId)
            ->with('kamar')
            ->orderBy('name');
    }

    private function abortUnlessPenghuniKos(User $penghuni, int $kosId): void
    {
        abort_unless($penghuni->peran === 'penghuni' && (int) $penghuni->kos_id === $kosId, 403);
    }
}
