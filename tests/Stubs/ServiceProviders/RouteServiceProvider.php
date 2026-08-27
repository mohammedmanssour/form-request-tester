<?php

namespace MohammedManssour\FormRequestTester\Tests\Stubs\ServiceProviders;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function map(): void
    {
        // load routes from here
        Route::put('posts/{post}', fn () => null);
    }
}
