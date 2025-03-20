<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\ResponseFactory;
use Laravel\Lumen\Http\ResponseFactory as LumenResponseFactory;

class ResponseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ResponseFactory::class, function () {
            return new LumenResponseFactory();
        });
    }
}
