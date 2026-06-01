<?php

// app/Http/Controllers/KomunikasiController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penghuni;
use App\Models\Pesan;
use App\Models\Broadcast;

class KomunikasiController extends Controller
{
    /**
     * Pilihan target broadcast.
     */
    private array $targetOptions = [
        'Semua Penghuni',
        'Lantai 1',
        'Lantai 2',
    ];

    /**
     * Halaman utama: daftar chat + form broadcast.
     */
    public function index()
    {
        // Ambil daftar penghuni beserta pesan terakhir
        $chats = Penghuni::query()
            ->withLastMessage()   // scope di Model Penghuni
            ->orderByDesc('last_message_at')
            ->get();

        // Riwayat broadcast terbaru
        $riwayatBroadcast = Broadcast::latest()->take(10)->get();

        return view('komunikasi.index', [
            'chats'            => $chats,
            'riwayatBroadcast' => $riwayatBroadcast,
            'targetOptions'    => $this->targetOptions,
            'selectedTarget'   => 'Semua Penghuni',
        ]);
    }

    /**
     * Halaman detail chat dengan satu penghuni.
     */
    public function show(int $penghuniId)
    {
        $penghuni = Penghuni::findOrFail($penghuniId);

        $messages = Pesan::where('penghuni_id', $penghuniId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                // Format waktu: "Kemarin 14:30" atau "HH:MM"
                $msg->waktu = $this->formatWaktu($msg->created_at);
                return $msg;
            });

        return view('komunikasi.show', compact('penghuni', 'messages'));
    }

    /**
     * Kirim pesan dari owner ke penghuni.
     */
    public function send(Request $request, int $penghuniId)
    {
        $request->validate([
            'pesan' => 'required|string|max:1000',
        ]);

        Pesan::create([
            'penghuni_id' => $penghuniId,
            'pengirim'    => 'owner',
            'pesan'       => $request->pesan,
        ]);

        return redirect()->route('komunikasi.show', $penghuniId);
    }

    /**
     * Kirim broadcast ke semua / lantai tertentu.
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'pesan'  => 'required|string|max:1000',
            'target' => 'required|string',
        ]);

        Broadcast::create([
            'pesan'   => $request->pesan,
            'target'  => $request->target,
            'tanggal' => now()->translatedFormat('d F'),  // "01 Juni"
        ]);

        // Jika ingin push notif ke penghuni, tambahkan logika di sini

        return redirect()->route('komunikasi.index')
                         ->with('success', 'Broadcast berhasil dikirim.');
    }

    /**
     * Format waktu pesan.
     */
    private function formatWaktu($datetime): string
    {
        $now   = now();
        $date  = \Carbon\Carbon::parse($datetime);

        if ($date->isToday()) {
            return 'Hari ini ' . $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'Kemarin ' . $date->format('H:i');
        }

        return $date->format('d M H:i');
    }
}