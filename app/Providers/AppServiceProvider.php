<?php

namespace App\Providers;

use App\Models\LaporanPerawatan;
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
    }
}
