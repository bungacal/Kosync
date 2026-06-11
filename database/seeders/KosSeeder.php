<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KosSeeder extends Seeder
{
    public function run(): void
    {
        $kosData = [

            // =====================================================
            // KOS PUTRI MUSLIM 2
            // =====================================================
            [
                'nama' => 'Kos Putri Muslim 2',

                'fasilitas_bersama' => [
                    'Dapur bersama',
                    'Parkiran Motor & Mobil',
                    'Jemuran Baju',
                    'Kulkas',
                ],

                'tipe_kamar' => [
                    [
                        'nama' => 'Tipe A',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Dalam',
                            'Wastefel',
                            'Lemari',
                            'Meja Belajar',
                            'Kasur',
                        ],
                        'kamar' => range(314, 323),
                    ],
                    [
                        'nama' => 'Tipe B',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Luar',
                            'Lemari',
                            'Meja Belajar',
                            'Kasur',
                        ],
                        'kamar' => range(101, 102),
                    ],
                    [
                        'nama' => 'Tipe C',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Dalam',
                            'Lemari',
                            'Meja Belajar',
                            'Kasur',
                        ],
                        'kamar' => range(201, 213),
                    ],
                ],
            ],

            // =====================================================
            // KOS PUTRI MUSLIM 1
            // =====================================================
            [
                'nama' => 'Kos Putri Muslim 1',

                'fasilitas_bersama' => [
                    'Dapur bersama',
                    'Kamar mandi luar',
                    'Parkiran motor',
                    'Area jemur pakaian',
                ],

                'tipe_kamar' => [
                    [
                        'nama' => 'Tipe A',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Dalam',
                        ],
                        'kamar' => range(301, 302),
                    ],
                    [
                        'nama' => 'Tipe B',
                        'fasilitas' => [
                            'Kipas',
                            'Kamar Mandi Luar',
                        ],
                        'kamar' => range(101, 122),
                    ],
                    [
                        'nama' => 'Tipe C',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Luar',
                        ],
                        'kamar' => range(223, 243),
                    ],
                ],
            ],

            // =====================================================
            // KOS PAKIS 77
            // =====================================================
            [
                'nama' => 'Kos Pakis 77',

                'fasilitas_bersama' => [
                    'WiFi',
                    'Dapur + Kulkas',
                    'Meja Makan',
                    'Parkir Motor',
                    'Laundry + Jemuran',
                ],

                'tipe_kamar' => [
                    [
                        'nama' => 'AC + KM Dalam',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Dalam',
                            'Meja',
                            'Kursi',
                            'Lemari',
                            'Springbed',
                        ],
                        'kamar' => [101, 102, 103, 104, 201, 202, 203],
                    ],
                    [
                        'nama' => 'Non AC + KM Luar',
                        'fasilitas' => [
                            'Kamar Mandi Luar',
                            'Meja',
                            'Kursi',
                            'Lemari',
                            'Springbed',
                        ],
                        'kamar' => [105, 106, 107],
                    ],
                ],
            ],

            // =====================================================
            // KOS WISPER 35
            // =====================================================
            [
                'nama' => 'Kos Wisper 35',

                'fasilitas_bersama' => [
                    'Dapur bersama',
                    'Kamar mandi luar',
                    'Parkiran motor',
                    'Area jemur pakaian',
                    'WiFi',
                ],

                'tipe_kamar' => [
                    [
                        'nama' => 'AC + KM Dalam',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Dalam',
                            'Meja',
                            'Kursi',
                            'Lemari',
                        ],
                        'kamar' => range(1, 6),
                    ],
                    [
                        'nama' => 'AC + KM Luar',
                        'fasilitas' => [
                            'AC',
                            'Kamar Mandi Luar',
                            'Meja',
                            'Kursi',
                            'Lemari',
                        ],
                        'kamar' => range(7, 12),
                    ],
                ],
            ],

            // =====================================================
            // KOS PRAMASHINTA
            // =====================================================
            [
                'nama' => 'Kos Pramashinta',

                'fasilitas_bersama' => [
                    'Dapur bersama',
                    'Parkiran motor',
                    'Area jemur pakaian',
                    'WiFi',
                    'Parkir Mobil',
                ],

                'tipe_kamar' => [
                    [
                        'nama' => 'Reguler',
                        'fasilitas' => [
                            'Meja',
                            'Kursi',
                            'Lemari',
                        ],
                        'kamar' => range(1, 9),
                    ],
                    [
                        'nama' => 'VIP',
                        'fasilitas' => [
                            'Meja',
                            'Kursi',
                            'Lemari',
                            'Water Heater',
                        ],
                        'kamar' => range(10, 18),
                    ],
                ],
            ],
        ];

        // =====================================================
        // PROSES INSERT KE DATABASE
        // =====================================================
        foreach ($kosData as $kos) {
            // 1. Insert kos
            $kosId = DB::table('kos')->insertGetId([
                'nama'       => $kos['nama'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Insert fasilitas bersama kos
            foreach ($kos['fasilitas_bersama'] as $fasilitas) {
                DB::table('fasilitas_kos')->insert([
                    'kos_id'     => $kosId,
                    'nama'       => $fasilitas,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Insert tipe kamar beserta kamar-kamarnya
            foreach ($kos['tipe_kamar'] as $tipe) {
                $tipeId = DB::table('tipe_kamar')->insertGetId([
                    'kos_id'     => $kosId,
                    'nama'       => $tipe['nama'],
                    'fasilitas'  => json_encode($tipe['fasilitas']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 4. Insert kamar untuk setiap tipe
                foreach ($tipe['kamar'] as $nomorKamar) {
                    // Tentukan lantai berdasarkan digit pertama nomor kamar
                    // Contoh: 101 → lantai 1, 201 → lantai 2, 1 → lantai 1
                    $lantai = strlen((string) $nomorKamar) >= 3
                        ? (string) intval(substr((string) $nomorKamar, 0, 1))
                        : '1';

                    DB::table('kamar')->insert([
                        'kos_id'       => $kosId,
                        'tipe_kamar_id' => $tipeId,
                        'nomor'        => (string) $nomorKamar,
                        'lantai'       => $lantai,
                        'status'       => 'Kosong',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }
    }
}
