<?php

namespace SalemAljebaly\FilamentMapPicker;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentMapPickerPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-map-picker';
    }

    public function register(Panel $panel): void
    {
        // Intentionally left minimal.
        // Assets are loaded only when the field view is rendered via Filament stacks.
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
