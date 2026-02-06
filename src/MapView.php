<?php

namespace SalemAljebaly\FilamentMapPicker;

use Filament\Infolists\Components\Entry;
use SalemAljebaly\FilamentMapPicker\Enums\TileProvider;
use SalemAljebaly\FilamentMapPicker\Support\TileProviders;

class MapView extends Entry
{
    protected string $view = 'filament-map-picker::infolists.components.map-view';

    protected string $latitudeField = 'latitude';
    protected string $longitudeField = 'longitude';

    protected float $defaultLatitude;
    protected float $defaultLongitude;
    protected int $defaultZoom;
    protected int $height;

    protected string $tileProvider;
    protected ?string $customTileUrl = null;
    protected ?string $customDarkTileUrl = null;
    protected bool $autoDarkMode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultLatitude = (float) config('filament-map-picker.default_location.lat', 24.7136);
        $this->defaultLongitude = (float) config('filament-map-picker.default_location.lng', 46.6753);
        $this->defaultZoom = (int) config('filament-map-picker.default_zoom', 10);
        $this->height = (int) config('filament-map-picker.height', 240);

        $this->tileProvider = (string) config('filament-map-picker.tile_provider', TileProvider::OpenStreetMap->value);
        $this->autoDarkMode = (bool) config('filament-map-picker.auto_dark_mode', true);
    }

    public function latlngFields(string $latitudeField, string $longitudeField): static
    {
        $this->latitudeField = $latitudeField;
        $this->longitudeField = $longitudeField;

        return $this;
    }

    public function defaultLocation(float $latitude, float $longitude): static
    {
        $this->defaultLatitude = $latitude;
        $this->defaultLongitude = $longitude;

        return $this;
    }

    public function defaultZoom(int $zoom): static
    {
        $this->defaultZoom = $zoom;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function tileProvider(string | TileProvider $provider): static
    {
        $this->tileProvider = $provider instanceof TileProvider ? $provider->value : $provider;

        return $this;
    }

    public function customTileUrl(string $url, ?string $darkUrl = null): static
    {
        $this->customTileUrl = $url;
        $this->customDarkTileUrl = $darkUrl;

        return $this;
    }

    public function autoDarkMode(bool $enabled = true): static
    {
        $this->autoDarkMode = $enabled;

        return $this;
    }

    public function getLatitudeField(): string
    {
        return $this->latitudeField;
    }

    public function getLongitudeField(): string
    {
        return $this->longitudeField;
    }

    public function getDefaultLatitude(): float
    {
        return $this->defaultLatitude;
    }

    public function getDefaultLongitude(): float
    {
        return $this->defaultLongitude;
    }

    public function getDefaultZoom(): int
    {
        return $this->defaultZoom;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getTileLightUrl(): string
    {
        return $this->customTileUrl ?? TileProviders::from($this->tileProvider)->lightUrl();
    }

    public function getTileDarkUrl(): string
    {
        if (filled($this->customDarkTileUrl)) {
            return $this->customDarkTileUrl;
        }

        if (filled($this->customTileUrl)) {
            return $this->customTileUrl;
        }

        return TileProviders::from($this->tileProvider)->darkUrl();
    }

    public function getTileAttribution(): string
    {
        return TileProviders::from($this->tileProvider)->attribution();
    }

    public function isAutoDarkMode(): bool
    {
        return $this->autoDarkMode;
    }
}
