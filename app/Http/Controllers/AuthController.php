<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'peran' => ['required', Rule::in(['pemilik', 'penghuni'])],
        ]);

        $credentials = [
            'email' => Str::lower($validated['email']),
            'password' => $validated['password'],
            'peran' => $validated['peran'],
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email, password, atau peran tidak sesuai.'])
                ->onlyInput('email', 'peran');
        }

        $request->session()->regenerate();

        return redirect()->route($this->homeRouteName(Auth::user()));
    }

    public function showPenghuniSignup(): View
    {
        return view('auth.signup-tenant', [
            'kosList' => Kos::query()->with(['kamar' => fn ($query) => $query->where('status', 'Kosong')->orderBy('nomor')])->orderBy('nama')->get(),
        ]);
    }

    public function signupPenghuni(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(6)],
            'kos_id' => ['required', 'exists:kos,id'],
            'kamar_id' => ['required', 'exists:kamar,id'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $kamar = Kamar::query()
                ->whereKey($validated['kamar_id'])
                ->where('kos_id', $validated['kos_id'])
                ->where('status', 'Kosong')
                ->lockForUpdate()
                ->first();

            if (! $kamar) {
                throw ValidationException::withMessages([
                    'kamar_id' => 'Kamar tidak tersedia.',
                ]);
            }

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'peran' => 'penghuni',
                'kos_id' => $validated['kos_id'],
                'kamar_id' => $kamar->id,
            ]);

            $kamar->update([
                'penghuni_id' => $user->id,
                'status' => 'Terisi',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('penghuni.home');
    }

    public function showPemilikSignup(): View
    {
        return view('auth.signup-owner');
    }

    public function signupPemilik(Request $request): RedirectResponse
    {
        $this->normalizeEmail($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(6)],
            'kos_name' => ['required', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'peran' => 'pemilik',
            ]);

            $kos = Kos::query()->create([
                'pemilik_id' => $user->id,
                'nama' => $validated['kos_name'],
            ]);

            $user->update(['kos_id' => $kos->id]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('pemilik.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeRouteName(?User $user): string
    {
        return $user?->peran === 'pemilik'
            ? 'pemilik.home'
            : 'penghuni.home';
    }

    private function normalizeEmail(Request $request): void
    {
        if ($request->filled('email')) {
            $request->merge([
                'email' => Str::lower((string) $request->input('email')),
            ]);
        }
    }
}
