<?php

namespace SalemAljebaly\FilamentMapPicker\Enums;

enum TileProvider: string
{
    case OpenStreetMap = 'osm';
    case CartoDbPositron = 'cartodb_light';
    case CartoDbDarkMatter = 'cartodb_dark';

    public function lightUrl(): string
    {
        return match ($this) {
            self::OpenStreetMap => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            self::CartoDbPositron => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
            self::CartoDbDarkMatter => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
        };
    }

    public function darkUrl(): string
    {
        return match ($this) {
            self::OpenStreetMap => self::CartoDbDarkMatter->lightUrl(),
            self::CartoDbPositron => self::CartoDbDarkMatter->lightUrl(),
            self::CartoDbDarkMatter => self::CartoDbDarkMatter->lightUrl(),
        };
    }

    public function attribution(): string
    {
        return match ($this) {
            self::OpenStreetMap => '© OpenStreetMap contributors',
            self::CartoDbPositron, self::CartoDbDarkMatter => '© OpenStreetMap contributors © CARTO',
        };
    }
}
