<?php

// app/Http/Controllers/LaporanController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan; // Sesuaikan dengan model Anda

class LaporanController extends Controller
{
    /**
     * Pilihan status filter (disesuaikan dengan data di DB).
     */
    private array $statusOptions = [
        'Semua Status',
        'Pending',
        'Diproses',
        'Selesai',
    ];

    /**
     * Pilihan kategori filter (disesuaikan dengan data di DB).
     */
    private array $kategoriOptions = [
        'Semua Kategori',
        'Listrik',
        'Plumbing',
        'AC',
    ];

    /**
     * Tampilkan daftar laporan dengan filter opsional.
     */
    public function index(Request $request)
    {
        $selectedStatus   = $request->input('status',   '');
        $selectedKategori = $request->input('kategori', '');

        // --- Bersihkan nilai "Semua …" menjadi kosong ---
        if (in_array($selectedStatus, ['', 'Semua Status'])) {
            $selectedStatus = '';
        }
        if (in_array($selectedKategori, ['', 'Semua Kategori'])) {
            $selectedKategori = '';
        }

        // --- Query dengan filter ---
        $query = Laporan::query();

        if ($selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        if ($selectedKategori !== '') {
            $query->where('kategori', $selectedKategori);
        }

        $laporan = $query->orderBy('created_at', 'desc')->get();

        return view('laporan.index', [
            'laporan'          => $laporan,
            'statusOptions'    => $this->statusOptions,
            'kategoriOptions'  => $this->kategoriOptions,
            'selectedStatus'   => $selectedStatus,
            'selectedKategori' => $selectedKategori,
        ]);
    }
}