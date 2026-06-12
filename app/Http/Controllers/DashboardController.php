<?php
namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Broadcast;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stat cards ──────────────────────────────────────────
        $laporanAktif   = Laporan::whereIn('status', ['Pending', 'Diproses'])->count();
        $pendingCount   = Laporan::where('status', 'Pending')->count();

        $selesaiBulanIni = Laporan::where('status', 'Selesai')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at',  now()->year)
            ->count();

        $selesaiMingguIni = Laporan::where('status', 'Selesai')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Rata-rata hari penyelesaian (dari created_at → updated_at saat Selesai)
        $avgPerbaikan = Laporan::where('status', 'Selesai')
            ->whereMonth('updated_at', now()->month)
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) / 24 as avg_hari')
            ->value('avg_hari');
        $avgPerbaikan = round($avgPerbaikan ?? 0, 1);

        // ── Laporan terbaru (4 item) ─────────────────────────────
        $laporanTerbaru = Laporan::latest()->take(4)->get();

        // ── Tren laporan 6 bulan terakhir ───────────────────────
        $tren = Laporan::selectRaw('
                MONTH(created_at)   as bulan,
                YEAR(created_at)    as tahun,
                COUNT(*)            as total
            ')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('tahun ASC, bulan ASC')
            ->get()
            ->map(fn($row) => [
                'label' => \Carbon\Carbon::createFromDate($row->tahun, $row->bulan, 1)
                                ->locale('id')->isoFormat('MMM'),
                'value' => $row->total,
            ]);

        // ── Kategori kerusakan ──────────────────────────────────
        $kategori = Laporan::selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        // ── Broadcast terbaru ───────────────────────────────────
        $broadcasts = Broadcast::latest()->take(3)->get();

        return view('dashboard', compact(
            'laporanAktif', 'pendingCount',
            'selesaiBulanIni', 'selesaiMingguIni',
            'avgPerbaikan',
            'laporanTerbaru',
            'tren',
            'kategori',
            'broadcasts',
        ));
    }
}