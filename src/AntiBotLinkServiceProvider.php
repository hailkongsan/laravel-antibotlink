<?php

namespace Hailkongsan\AntiBotLink;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;

class AntiBotLinkServiceProvider extends ServiceProvider
{
	public function register()
	{

	}

	public function boot()
	{
		$this->mergeConfigFrom(__DIR__.'/config.php', 'antibotlink');

		$this->publishes([
	        __DIR__.'/config.php' => config_path('antibotlink.php')
		], 'config');
		
	    // $this->publishes([
	    // 	__DIR__.'/assets/fonts' => config('antibotlink.assets.font_path')
	    // ], 'public');

	    $this->loadTranslationsFrom(__DIR__.'/translations', 'antibotlink');

	    $this->publishes([
	        __DIR__.'/translations' => resource_path('lang/antibotlink'),
	    ], 'resource');

	    $this->app->singleton('antibotlink', function($app) {
	    	return new AntiBotLink();
	    });

	    Validator::extend('antibotlink', function($attribute, $value) {
	    	return $this->app['antibotlink']->verify($value);
	    });
	}

	public function provides()
    {
        return ['antibotlink'];
    }
}
