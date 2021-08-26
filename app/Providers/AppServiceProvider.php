<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        try {
            $time_zone=\App\Model\BusinessSetting::where('key','time_zone')->first();
            $time_zone = $time_zone->value ?? 'UTC';
            // date_default_timezone_set($time_zone);
            config(['app.timezone' => $time_zone]);
        }catch(\Exception $exception){}
    }
}
