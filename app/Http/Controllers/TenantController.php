<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TenantController extends Controller
{
    public function home(): View
    {
        $tenant = $this->tenant();

        return view('tenant.home', [
            'tenant' => $tenant,
            'activeReports' => collect(),
            'finishedReports' => collect(),
        ]);
    }

    private function tenant()
    {
        abort_unless(auth()->user()?->role === 'tenant', 403);

        return auth()->user()->load(['kos', 'room']);
    }
}
