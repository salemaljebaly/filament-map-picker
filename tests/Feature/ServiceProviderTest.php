<?php

namespace SalemAljebaly\FilamentMapPicker\Tests\Feature;

use SalemAljebaly\FilamentMapPicker\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_it_loads_views(): void
    {
        $this->assertTrue(view()->exists('filament-map-picker::forms.components.map-picker'));
    }

    public function test_it_merges_config_defaults(): void
    {
        $this->assertSame(24.7136, (float) config('filament-map-picker.default_location.lat'));
        $this->assertSame(46.6753, (float) config('filament-map-picker.default_location.lng'));
    }
}
