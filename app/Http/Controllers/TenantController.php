<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function home(): View
    {
        $tenant = $this->tenant();

        return view('tenant.home', [
            'tenant' => $tenant,
            'activeReports' => $this->reportQuery()->whereIn('status', ['Pending', 'Diproses'])->latest()->get(),
            'finishedReports' => $this->reportQuery()->where('status', 'Selesai')->latest()->take(3)->get(),
        ]);
    }

    public function reports(): View
    {
        return view('tenant.reports', [
            'tenant' => $this->tenant(),
            'activeReports' => $this->reportQuery()->whereIn('status', ['Pending', 'Diproses'])->latest()->get(),
        ]);
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $tenant = $this->tenant();
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'issue' => ['required', 'string', 'max:255'],
        ]);

        MaintenanceReport::query()->create([
            'kos_id' => $tenant->kos_id,
            'tenant_id' => $tenant->id,
            'room_id' => $tenant->room_id,
            'issue' => $validated['issue'],
            'category' => $validated['category'],
            'status' => 'Pending',
        ]);

        return back()->with('status', 'Laporan berhasil dibuat.');
    }

    public function history(): View
    {
        return view('tenant.history', [
            'tenant' => $this->tenant(),
            'reports' => $this->reportQuery()->where('status', 'Selesai')->latest()->get(),
        ]);
    }

    public function rateReport(Request $request, MaintenanceReport $report): RedirectResponse
    {
        $this->tenant();
        abort_unless($report->tenant_id === auth()->id(), 403);
        abort_unless($report->status === 'Selesai', 422);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $report->update(['rating' => $validated['rating']]);

        return back()->with('status', 'Rating berhasil dikirim.');
    }

    private function tenant()
    {
        abort_unless(auth()->user()?->role === 'tenant', 403);

        return auth()->user()->load(['kos', 'room']);
    }

    private function reportQuery()
    {
        return MaintenanceReport::query()
            ->with(['room', 'kos'])
            ->where('tenant_id', auth()->id());
    }
}
