<?php

namespace WallE\LaravelImageresize;

use Illuminate\Support\ServiceProvider;

class ImageResizerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/imageresizer.php', 'imageresizer');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/imageresizer.php' => config_path('imageresizer.php'),
        ], 'config');
    }
}
