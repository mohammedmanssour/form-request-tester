<?php

namespace MohammedManssour\FormRequestTester\Tests\Stubs\ServiceProviders;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        // load routes from here
        Route::put('posts/{post}', 'PostsController@update');
    }

}