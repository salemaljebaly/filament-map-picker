<?php

namespace SalemAljebaly\FilamentMapPicker;

use Illuminate\Support\ServiceProvider;

class FilamentMapPickerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-map-picker.php', 'filament-map-picker');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-map-picker');

        $this->publishes([
            __DIR__ . '/../config/filament-map-picker.php' => config_path('filament-map-picker.php'),
        ], 'filament-map-picker-config');
    }
}
