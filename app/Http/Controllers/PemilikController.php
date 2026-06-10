<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\LaporanPerawatan;
use Illuminate\View\View;

class PemilikController extends Controller
{
    public function home(): View
    {
        abort_unless(auth()->user()?->peran === 'pemilik', 403);
        $kos = auth()->user()->kosMilik()->with('kamar')->first();

        return view('owner.home', [
            'kos' => $kos,
            'laporanAktif' => $kos
                ? LaporanPerawatan::query()->where('kos_id', $kos->id)->whereIn('status', ['Pending', 'Diproses'])->latest()->get()
                : collect(),
            'broadcastTerbaru' => $kos
                ? Broadcast::query()->where('kos_id', $kos->id)->latest()->take(3)->get()
                : collect(),
        ]);
    }
}
