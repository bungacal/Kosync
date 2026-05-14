<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', Rule::in(['owner', 'tenant'])],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email, password, atau role tidak sesuai.'])
                ->onlyInput('email', 'role');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeRoute());
    }

    public function showTenantSignup(): View
    {
        return view('auth.signup-tenant', [
            'kosList' => Kos::query()->with(['rooms' => fn ($query) => $query->where('status', 'Kosong')->orderBy('number')])->orderBy('name')->get(),
        ]);
    }

    public function signupTenant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'kos_id' => ['required', 'exists:kos,id'],
            'room_id' => ['required', 'exists:rooms,id'],
        ]);

        $room = Room::query()
            ->whereKey($validated['room_id'])
            ->where('kos_id', $validated['kos_id'])
            ->where('status', 'Kosong')
            ->first();

        if (! $room) {
            return back()
                ->withErrors(['room_id' => 'Kamar tidak tersedia.'])
                ->withInput();
        }

        $user = DB::transaction(function () use ($validated, $room) {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'tenant',
                'kos_id' => $validated['kos_id'],
                'room_id' => $room->id,
            ]);

            $room->update([
                'tenant_id' => $user->id,
                'status' => 'Terisi',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tenant.home');
    }

    public function showOwnerSignup(): View
    {
        return view('auth.signup-owner');
    }

    public function signupOwner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'kos_name' => ['required', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'owner',
            ]);

            $kos = Kos::query()->create([
                'owner_id' => $user->id,
                'name' => $validated['kos_name'],
            ]);

            $user->update(['kos_id' => $kos->id]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('owner.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeRoute(): string
    {
        return Auth::user()?->role === 'owner'
            ? route('owner.home')
            : route('tenant.home');
    }
}
