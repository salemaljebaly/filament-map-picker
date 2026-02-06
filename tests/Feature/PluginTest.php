<?php

namespace SalemAljebaly\FilamentMapPicker\Tests\Feature;

use SalemAljebaly\FilamentMapPicker\FilamentMapPickerPlugin;
use SalemAljebaly\FilamentMapPicker\Tests\TestCase;

class PluginTest extends TestCase
{
    public function test_plugin_has_id(): void
    {
        $this->assertSame('filament-map-picker', FilamentMapPickerPlugin::make()->getId());
    }
}
