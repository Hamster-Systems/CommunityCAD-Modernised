<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Models\Call;
use App\Models\Cad\ActiveUnit;
use App\Support\Cad\CadAudioPublisher;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Call::created([CadAudioPublisher::class, 'callCreated']);
        Call::updated([CadAudioPublisher::class, 'callUpdated']);
        ActiveUnit::updated([CadAudioPublisher::class, 'unitUpdated']);

        $this->app->bind('cad_settings', function () {
            return Cache::rememberForever('cad_settings', function () {
                return DB::table('cad_settings')->get(['name', 'value']);
            });
        });
    }
}
