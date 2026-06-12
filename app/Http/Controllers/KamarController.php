<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Laporan;

class KamarController extends Controller
{
    public function daftarKamar()
    {
        $kamarDenganLaporan = Laporan::whereIn('status', ['Pending', 'Diproses'])
            ->pluck('kamar')
            ->toArray();

        $kamarList = User::where('role', 'penghuni')
            ->whereNotNull('nomor_kamar')
            ->get()
            ->map(function ($user) use ($kamarDenganLaporan) {
                return [
                    'id'          => $user->id,
                    'penghuni'    => $user->name,
                    'nomor_kamar' => $user->nomor_kamar,
                    'lantai'      => 'Lantai ' . substr($user->nomor_kamar, 0, 1),
                    'harga'       => 1200000,
                    'status'      => in_array($user->nomor_kamar, $kamarDenganLaporan)
                                        ? 'laporan' : 'terisi',
                ];
            });

        $lantaiList = $kamarList->pluck('lantai')->unique()->sort()->values();

        return view('owner.daftar-kamar', [
            'kamarList'   => $kamarList,
            'lantaiList'  => $lantaiList,
            'totalKamar'  => $kamarList->count(),
            'terisi'      => $kamarList->where('status', 'terisi')->count(),
            'kosong'      => 0,
            'adaLaporan'  => $kamarList->where('status', 'laporan')->count(),
            'maintenance' => 0,
            'page'        => 'daftar-kamar',
            'kos'         => auth()->user()->kos ?? null,
        ]);
    }

    public function denahLantai()
    {
        $kamarDenganLaporan = Laporan::whereIn('status', ['Pending', 'Diproses'])
            ->pluck('kamar')
            ->toArray();

        $users = User::where('role', 'penghuni')
            ->whereNotNull('nomor_kamar')
            ->get();

        $kamarPerLantai = $users->map(function ($user) use ($kamarDenganLaporan) {
                $user->status   = in_array($user->nomor_kamar, $kamarDenganLaporan)
                                    ? 'laporan' : 'terisi';
                $user->lantai   = 'Lantai ' . substr($user->nomor_kamar, 0, 1);
                $user->penghuni = $user->name;
                return $user;
            })->groupBy('lantai');

        $roomDetail = $users->mapWithKeys(function ($user) use ($kamarDenganLaporan) {
            $laporanAktif = Laporan::where('kamar', $user->nomor_kamar)
                ->whereIn('status', ['Pending', 'Diproses'])
                ->value('masalah');

            return [$user->id => [
                'nomor_kamar'   => $user->nomor_kamar,
                'lantai'        => 'Lantai ' . substr($user->nomor_kamar, 0, 1),
                'status'        => in_array($user->nomor_kamar, $kamarDenganLaporan)
                                      ? 'laporan' : 'terisi',
                'penghuni'      => $user->name,
                'penghuni_id'   => $user->id,
                'sejak'         => optional($user->created_at)->format('M Y'),
                'harga'         => 1200000,
                'ukuran'        => '3 × 4 m²',
                'tipe'          => 'Kamar standar',
                'fasilitas'     => ['AC', 'WiFi', 'Kamar mandi dalam'],
                'laporan_aktif' => $laporanAktif,
            ]];
        });

        return view('owner.denah-lantai', [
            'kamarPerLantai' => $kamarPerLantai,
            'roomDetail'     => $roomDetail,
            'totalKamar'     => $users->count(),
            'terisi'         => $users->count(),
            'kosong'         => 0,
            'adaLaporan'     => count($kamarDenganLaporan),
            'page'           => 'denah-lantai',
            'kos'            => auth()->user()->kos ?? null,
        ]);
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)->where('role', 'penghuni')->firstOrFail();
        $user->nomor_kamar = null;
        $user->save();

        return response()->json(['success' => true]);
    }
}