<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
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
        Validator::extend('alpha_space', function($attribute, $value){
            return preg_match('@^[A-Z\s]+$@', $value);
        });
        Validator::extend('phone', function($attribute, $value){
            return preg_match('@^[\d\+]+$@', $value);
        });
    }
}
