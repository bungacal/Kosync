<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return auth()->user()?->peran === 'pemilik'
            ? redirect()->route('pemilik.home')
            : redirect()->route('penghuni.home');
    }
}
