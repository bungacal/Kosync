<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OwnerController extends Controller
{
    public function home(): View
    {
        abort_unless(auth()->user()?->role === 'owner', 403);

        return view('owner.home', [
            'kos' => auth()->user()->ownedKos()->with('rooms')->first(),
        ]);
    }
}
