<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\LaporanPerawatan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class KamarController extends Controller
{
    public function daftarKamar()
    {
        $kos = $this->kosPemilik();
        $kamarDenganLaporan = $this->kamarDenganLaporan($kos->id);

        $kamarList = Kamar::query()
            ->with('penghuni')
            ->withCount('laporanPerawatan')
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
                    'harga' => $kamar->harga,
                    'status' => $status,
                    'can_delete' => ! $kamar->penghuni_id && $kamar->laporan_perawatan_count === 0,
                ];
            });

        $lantaiList = $kamarList->pluck('lantai')->unique()->sort()->values();
        $kamarKosong = Kamar::query()
            ->where('kos_id', $kos->id)
            ->where('status', 'Kosong')
            ->whereNull('penghuni_id')
            ->orderBy('lantai')
            ->orderBy('nomor')
            ->get(['id', 'nomor', 'lantai']);

        return view('owner.daftar-kamar', [
            'kamarList' => $kamarList,
            'lantaiList' => $lantaiList,
            'kamarKosong' => $kamarKosong,
            'totalKamar' => $kamarList->count(),
            'terisi' => $kamarList->whereIn('status', ['terisi', 'laporan'])->count(),
            'kosong' => $kamarList->where('status', 'kosong')->count(),
            'adaLaporan' => $kamarList->where('status', 'laporan')->count(),
            'maintenance' => $kamarList->where('status', 'maintenance')->count(),
            'page' => 'daftar-kamar',
            'kos' => $kos,
        ]);
    }

    public function storePenghuni(Request $request): RedirectResponse
    {
        $kos = $this->kosPemilik();

        if ($request->filled('email')) {
            $request->merge([
                'email' => Str::lower((string) $request->input('email')),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(6)],
            'kamar_id' => ['required', 'integer', 'exists:kamar,id'],
        ], [
            'name.required' => 'Nama penghuni wajib diisi.',
            'email.required' => 'Email penghuni wajib diisi.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'password.required' => 'Password awal wajib diisi.',
            'kamar_id.required' => 'Pilih kamar kosong untuk penghuni.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'tambahPenghuni')
                ->withInput()
                ->with('openTambahPenghuni', true);
        }

        try {
            DB::transaction(function () use ($validator, $kos) {
                $validated = $validator->validated();

                $kamar = Kamar::query()
                    ->whereKey($validated['kamar_id'])
                    ->where('kos_id', $kos->id)
                    ->where('status', 'Kosong')
                    ->whereNull('penghuni_id')
                    ->lockForUpdate()
                    ->first();

                if (! $kamar) {
                    throw ValidationException::withMessages([
                        'kamar_id' => 'Kamar ini sudah tidak tersedia.',
                    ]);
                }

                $penghuni = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'peran' => 'penghuni',
                    'kos_id' => $kos->id,
                    'kamar_id' => $kamar->id,
                ]);

                $kamar->update([
                    'penghuni_id' => $penghuni->id,
                    'status' => 'Terisi',
                ]);
            });
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors(), 'tambahPenghuni')
                ->withInput()
                ->with('openTambahPenghuni', true);
        }

        return redirect()
            ->route('pemilik.daftar-kamar')
            ->with('success', 'Penghuni baru berhasil ditambahkan.');
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
                'id' => $kamar->id,
                'nomor_kamar' => $kamar->nomor,
                'lantai' => $kamar->lantai,
                'status' => $this->statusKamar($kamar, $kamarDenganLaporan),
                'status_asli' => Str::lower($kamar->status),
                'penghuni' => $kamar->penghuni?->name,
                'penghuni_id' => $kamar->penghuni_id,
                'sejak' => optional($kamar->updated_at)->format('M Y'),
                'harga' => $kamar->harga,
                'ukuran' => $kamar->ukuran,
                'panjang' => $this->ukuranPart($kamar->ukuran, 0),
                'lebar' => $this->ukuranPart($kamar->ukuran, 1),
                'tipe' => $kamar->tipe,
                'fasilitas' => $kamar->fasilitas ?? [],
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
            'maintenance' => $kamar->where('status', 'Maintenance')->count(),
            'page' => 'denah-lantai',
            'kos' => $kos,
        ]);
    }

    public function storeKamar(Request $request): RedirectResponse
    {
        $kos = $this->kosPemilik();

        $request->merge([
            'nomor' => preg_replace('/\s+/', '', trim((string) $request->input('nomor'))),
            'lantai' => $this->normalizeLantai($request->input('lantai')),
            'harga' => preg_replace('/\D+/', '', (string) $request->input('harga')),
            'ukuran' => $this->formatUkuran($request->input('panjang'), $request->input('lebar')),
            'tipe' => preg_replace('/\s+/', ' ', trim((string) $request->input('tipe'))),
            'fasilitas' => $this->normalizeFasilitas($request->input('fasilitas')),
            'status' => Str::title(Str::lower(trim((string) $request->input('status', 'Kosong')))),
        ]);

        $validator = Validator::make($request->all(), [
            'nomor' => ['required', 'string', 'max:20'],
            'lantai' => ['required', 'string', 'max:50'],
            'harga' => ['required', 'integer', 'min:0'],
            'panjang' => ['required', 'numeric', 'min:1', 'max:20'],
            'lebar' => ['required', 'numeric', 'min:1', 'max:20'],
            'ukuran' => ['required', 'string', 'max:50'],
            'tipe' => ['required', 'string', 'max:100'],
            'fasilitas' => ['required', 'array', 'min:1'],
            'fasilitas.*' => ['string', 'max:50'],
            'status' => ['required', 'in:Kosong,Maintenance'],
        ], [
            'nomor.required' => 'Nomor kamar wajib diisi.',
            'lantai.required' => 'Lantai kamar wajib diisi.',
            'harga.required' => 'Harga kamar wajib diisi.',
            'panjang.required' => 'Panjang kamar wajib diisi.',
            'lebar.required' => 'Lebar kamar wajib diisi.',
            'tipe.required' => 'Tipe kamar wajib diisi.',
            'fasilitas.required' => 'Pilih minimal satu fasilitas kamar.',
            'fasilitas.min' => 'Pilih minimal satu fasilitas kamar.',
        ]);

        $validator->after(function ($validator) use ($request, $kos) {
            $exists = Kamar::query()
                ->where('kos_id', $kos->id)
                ->where('nomor', $request->input('nomor'))
                ->exists();

            if ($exists) {
                $validator->errors()->add('nomor', 'Nomor kamar ini sudah ada.');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'tambahKamar')
                ->withInput()
                ->with('openTambahKamar', true);
        }

        Kamar::query()->create([
            'kos_id' => $kos->id,
            'nomor' => $validator->validated()['nomor'],
            'lantai' => $validator->validated()['lantai'],
            'harga' => $validator->validated()['harga'],
            'ukuran' => $validator->validated()['ukuran'] ?? null,
            'tipe' => $validator->validated()['tipe'],
            'fasilitas' => $validator->validated()['fasilitas'] ?? [],
            'status' => $validator->validated()['status'],
        ]);

        return redirect()
            ->route('pemilik.denah-lantai')
            ->with('success', 'Kamar baru berhasil ditambahkan.');
    }

    public function updateKamar(Request $request, int $id): RedirectResponse
    {
        $kos = $this->kosPemilik();
        $kamar = Kamar::query()
            ->where('kos_id', $kos->id)
            ->findOrFail($id);

        $request->merge([
            'nomor' => preg_replace('/\s+/', '', trim((string) $request->input('nomor'))),
            'lantai' => $this->normalizeLantai($request->input('lantai')),
            'harga' => preg_replace('/\D+/', '', (string) $request->input('harga')),
            'ukuran' => $this->formatUkuran($request->input('panjang'), $request->input('lebar')),
            'tipe' => preg_replace('/\s+/', ' ', trim((string) $request->input('tipe'))),
            'fasilitas' => $this->normalizeFasilitas($request->input('fasilitas')),
            'status' => Str::title(Str::lower(trim((string) $request->input('status', $kamar->status)))),
        ]);

        $validator = Validator::make($request->all(), [
            'nomor' => ['required', 'string', 'max:20'],
            'lantai' => ['required', 'string', 'max:50'],
            'harga' => ['required', 'integer', 'min:0'],
            'panjang' => ['required', 'numeric', 'min:1', 'max:20'],
            'lebar' => ['required', 'numeric', 'min:1', 'max:20'],
            'ukuran' => ['required', 'string', 'max:50'],
            'tipe' => ['required', 'string', 'max:100'],
            'fasilitas' => ['nullable', 'array'],
            'fasilitas.*' => ['string', 'max:50'],
            'status' => [
                'required',
                $kamar->penghuni_id
                    ? 'in:Terisi,Maintenance'
                    : 'in:Kosong,Maintenance',
            ],
        ]);

        $validator->after(function ($validator) use ($request, $kos, $kamar) {
            $exists = Kamar::query()
                ->where('kos_id', $kos->id)
                ->where('nomor', $request->input('nomor'))
                ->where('id', '!=', $kamar->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('nomor', 'Nomor kamar ini sudah ada.');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'editKamar')
                ->withInput()
                ->with('openEditKamar', $kamar->id);
        }

        $kamar->update([
            'nomor' => $validator->validated()['nomor'],
            'lantai' => $validator->validated()['lantai'],
            'harga' => $validator->validated()['harga'],
            'ukuran' => $validator->validated()['ukuran'],
            'tipe' => $validator->validated()['tipe'],
            'fasilitas' => $validator->validated()['fasilitas'] ?? [],
            'status' => $validator->validated()['status'],
        ]);

        return redirect()
            ->route('pemilik.denah-lantai')
            ->with('success', 'Informasi kamar berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $kos = $this->kosPemilik();
        $kamar = Kamar::query()
            ->where('kos_id', $kos->id)
            ->findOrFail($id);

        if ($kamar->penghuni_id || $kamar->status === 'Terisi') {
            return response()->json([
                'message' => 'Kamar yang masih memiliki penghuni tidak dapat dihapus.',
            ], 422);
        }

        $kamar->delete();

        return response()->json(['success' => true]);
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $kos = $this->kosPemilik();
        $validated = $request->validate([
            'kamar_ids' => ['required', 'array', 'min:1'],
            'kamar_ids.*' => ['integer'],
        ], [
            'kamar_ids.required' => 'Pilih minimal satu kamar untuk dihapus.',
        ]);

        $selectedIds = collect($validated['kamar_ids'])->map(fn ($id) => (int) $id)->unique();
        $deletableIds = Kamar::query()
            ->where('kos_id', $kos->id)
            ->whereIn('id', $selectedIds)
            ->whereNull('penghuni_id')
            ->whereDoesntHave('laporanPerawatan')
            ->pluck('id');

        Kamar::query()
            ->where('kos_id', $kos->id)
            ->whereIn('id', $deletableIds)
            ->delete();

        $skipped = $selectedIds->count() - $deletableIds->count();
        $message = $deletableIds->count() . ' kamar berhasil dihapus.';

        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' kamar dilewati karena masih memiliki penghuni atau riwayat laporan.';
        }

        return redirect()
            ->route('pemilik.daftar-kamar')
            ->with('success', $message);
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

        return match ($kamar->status) {
            'Terisi' => 'terisi',
            'Maintenance' => 'maintenance',
            default => 'kosong',
        };
    }

    private function normalizeLantai(mixed $lantai): string
    {
        $lantai = preg_replace('/\s+/', ' ', trim((string) $lantai));

        if ($lantai === '') {
            return '';
        }

        return Str::startsWith(Str::lower($lantai), 'lantai')
            ? Str::title($lantai)
            : 'Lantai ' . $lantai;
    }

    private function normalizeFasilitas(mixed $fasilitas): array
    {
        $items = is_array($fasilitas)
            ? $fasilitas
            : explode(',', (string) $fasilitas);

        return collect($items)
            ->map(fn (string $item) => preg_replace('/\s+/', ' ', trim($item)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function formatUkuran(mixed $panjang, mixed $lebar): string
    {
        $panjang = trim((string) $panjang);
        $lebar = trim((string) $lebar);

        if ($panjang === '' || $lebar === '') {
            return '';
        }

        return $panjang . ' x ' . $lebar . ' m';
    }

    private function ukuranPart(?string $ukuran, int $index): string
    {
        preg_match('/([\d.]+)\s*x\s*([\d.]+)/i', (string) $ukuran, $matches);

        return $matches[$index + 1] ?? ($index === 0 ? '3' : '4');
    }
}
