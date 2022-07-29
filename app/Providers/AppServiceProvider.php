<?php

namespace App\Providers;

use App\Services\NanoidService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(NanoidService::class, function () {
            return new NanoidService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Str::macro('initials', function ($value, $sep = ' ', $glue = '') {
            $collection = collect(explode($sep, $value));

            if ($collection->count() > 1) {
                $collection = $collection->map(function ($segment) {
                    return strtoupper($segment[0]) ?? '';
                });
                $collection->splice(2);
            } else {
                $collection = $collection->map(function ($segment) {
                    return substr(strtoupper($segment), 0, 2);
                });
            }

            return $collection->join($glue);
        });
    }
}
