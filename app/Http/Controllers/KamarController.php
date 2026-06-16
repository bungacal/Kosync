<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\LaporanPerawatan;

class KamarController extends Controller
{
    public function daftarKamar()
    {
        $kos = $this->kosPemilik();
        $kamarDenganLaporan = $this->kamarDenganLaporan($kos->id);

        $kamarList = Kamar::query()
            ->with('penghuni')
            ->where('kos_id', $kos->id)
            ->orderBy('lantai')
            ->orderBy('nomor')
            ->get()
            ->map(function (Kamar $kamar) use ($kamarDenganLaporan) {
                $status = $this->statusKamar($kamar, $kamarDenganLaporan);

                return [
                    'id' => $kamar->id,
                    'penghuni' => $kamar->penghuni?->name,
                    'nomor_kamar' => $kamar->nomor,
                    'lantai' => $kamar->lantai,
                    'harga' => 1200000,
                    'status' => $status,
                ];
            });

        $lantaiList = $kamarList->pluck('lantai')->unique()->sort()->values();

        return view('owner.daftar-kamar', [
            'kamarList' => $kamarList,
            'lantaiList' => $lantaiList,
            'totalKamar' => $kamarList->count(),
            'terisi' => $kamarList->whereIn('status', ['terisi', 'laporan'])->count(),
            'kosong' => $kamarList->where('status', 'kosong')->count(),
            'adaLaporan' => $kamarList->where('status', 'laporan')->count(),
            'maintenance' => 0,
            'page' => 'daftar-kamar',
            'kos' => $kos,
        ]);
    }

    public function denahLantai()
    {
        $kos = $this->kosPemilik();
        $kamarDenganLaporan = $this->kamarDenganLaporan($kos->id);

        $kamar = Kamar::query()
            ->with('penghuni')
            ->where('kos_id', $kos->id)
            ->orderBy('lantai')
            ->orderBy('nomor')
            ->get();

        $kamarPerLantai = $kamar
            ->map(function (Kamar $kamar) use ($kamarDenganLaporan) {
                $kamar->status_tampilan = $this->statusKamar($kamar, $kamarDenganLaporan);
                $kamar->penghuni_nama = $kamar->penghuni?->name;
                $kamar->nomor_kamar = $kamar->nomor;

                return $kamar;
            })
            ->groupBy('lantai');

        $roomDetail = $kamar->mapWithKeys(function (Kamar $kamar) use ($kamarDenganLaporan) {
            $laporanAktif = LaporanPerawatan::query()
                ->where('kamar_id', $kamar->id)
                ->whereIn('status', ['Pending', 'Diproses'])
                ->value('masalah');

            return [$kamar->id => [
                'nomor_kamar' => $kamar->nomor,
                'lantai' => $kamar->lantai,
                'status' => $this->statusKamar($kamar, $kamarDenganLaporan),
                'penghuni' => $kamar->penghuni?->name,
                'penghuni_id' => $kamar->penghuni_id,
                'sejak' => optional($kamar->updated_at)->format('M Y'),
                'harga' => 1200000,
                'ukuran' => '3 x 4 m2',
                'tipe' => 'Kamar standar',
                'fasilitas' => ['AC', 'WiFi', 'Kamar mandi dalam'],
                'laporan_aktif' => $laporanAktif,
            ]];
        });

        return view('owner.denah-lantai', [
            'kamarPerLantai' => $kamarPerLantai,
            'roomDetail' => $roomDetail,
            'totalKamar' => $kamar->count(),
            'terisi' => $kamar->where('status', 'Terisi')->count(),
            'kosong' => $kamar->where('status', 'Kosong')->count(),
            'adaLaporan' => count($kamarDenganLaporan),
            'page' => 'denah-lantai',
            'kos' => $kos,
        ]);
    }

    public function destroy($id)
    {
        $kos = $this->kosPemilik();

        Kamar::query()
            ->where('kos_id', $kos->id)
            ->findOrFail($id)
            ->update([
                'penghuni_id' => null,
                'status' => 'Kosong',
            ]);

        return response()->json(['success' => true]);
    }

    private function kosPemilik()
    {
        abort_unless(auth()->user()?->peran === 'pemilik', 403);

        return auth()->user()->kosMilik()->firstOrFail();
    }

    private function kamarDenganLaporan(int $kosId): array
    {
        return LaporanPerawatan::query()
            ->where('kos_id', $kosId)
            ->whereIn('status', ['Pending', 'Diproses'])
            ->pluck('kamar_id')
            ->all();
    }

    private function statusKamar(Kamar $kamar, array $kamarDenganLaporan): string
    {
        if (in_array($kamar->id, $kamarDenganLaporan, true)) {
            return 'laporan';
        }

        return $kamar->status === 'Terisi' ? 'terisi' : 'kosong';
    }
}
