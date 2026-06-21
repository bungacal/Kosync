<?php

namespace App\Providers;

use App\Models\LaporanPerawatan;
use App\Models\NotifikasiPenghuni;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('layouts.owner', function ($view) {
            $user = Auth::user();
            $notifications = collect();

            if ($user?->peran === 'pemilik') {
                $kos = $user->kosMilik()->first();

                if ($kos) {
                    $notifications = LaporanPerawatan::query()
                        ->with(['kamar:id,nomor', 'penghuni:id,name'])
                        ->where('kos_id', $kos->id)
                        ->whereIn('status', ['Pending', 'Diproses'])
                        ->latest()
                        ->take(5)
                        ->get();
                }
            }

            $view->with('ownerNotifications', $notifications);
        });

        View::composer('layouts.tenant', function ($view) {
            $user = Auth::user();
            $notifications = collect();
            $unreadCount = 0;
            $pendingRatingReport = null;

            if ($user?->peran === 'penghuni') {
                $notifications = NotifikasiPenghuni::query()
                    ->with(['laporan.kamar'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get();

                $unreadCount = NotifikasiPenghuni::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $pendingRatingReport = LaporanPerawatan::query()
                    ->with('kamar')
                    ->where('penghuni_id', $user->id)
                    ->where('status', 'Selesai')
                    ->whereNull('nilai_rating')
                    ->latest('selesai_pada')
                    ->first();
            }

            $view->with([
                'tenantNotifications' => $notifications,
                'tenantUnreadNotifications' => $unreadCount,
                'tenantPendingRatingReport' => $pendingRatingReport,
            ]);
        });
    }
}
