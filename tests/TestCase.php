<?php

namespace SalemAljebaly\FilamentMapPicker\Tests;

use SalemAljebaly\FilamentMapPicker\FilamentMapPickerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentMapPickerServiceProvider::class,
        ];
    }
}
