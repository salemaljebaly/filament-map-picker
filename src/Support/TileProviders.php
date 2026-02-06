<?php

namespace SalemAljebaly\FilamentMapPicker\Support;

use SalemAljebaly\FilamentMapPicker\Enums\TileProvider;

class TileProviders
{
    public static function from(string $provider): TileProvider
    {
        return TileProvider::tryFrom($provider) ?? TileProvider::OpenStreetMap;
    }
}
