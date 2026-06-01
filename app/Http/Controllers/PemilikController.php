<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PemilikController extends Controller
{
    public function home(): View
    {
        abort_unless(auth()->user()?->peran === 'pemilik', 403);

        return view('owner.home', [
            'kos' => auth()->user()->kosMilik()->with('kamar')->first(),
        ]);
    }
}
