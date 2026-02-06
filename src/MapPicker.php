<?php

namespace SalemAljebaly\FilamentMapPicker;

use Filament\Forms\Components\Field;
use SalemAljebaly\FilamentMapPicker\Enums\ControlPosition;
use SalemAljebaly\FilamentMapPicker\Enums\TileProvider;
use SalemAljebaly\FilamentMapPicker\Support\TileProviders;

class MapPicker extends Field
{
    protected string $view = 'filament-map-picker::forms.components.map-picker';

    protected string $wireModelPrefix = 'data';

    protected float $defaultLatitude;
    protected float $defaultLongitude;
    protected int $defaultZoom;

    protected int $height;

    protected bool $draggable;
    protected bool $searchable;
    protected bool $searchIsCollapsible;

    protected string $latitudeField = 'latitude';
    protected string $longitudeField = 'longitude';

    protected string $tileProvider;
    protected ?string $customTileUrl = null;
    protected ?string $customDarkTileUrl = null;
    protected bool $autoDarkMode;

    protected bool $showMyLocationControl;
    protected bool $showFullscreenControl;
    protected bool $showResetControl;

    protected string $controlPosition;

    protected string $markerColor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);

        $this->defaultLatitude = (float) config('filament-map-picker.default_location.lat', 24.7136);
        $this->defaultLongitude = (float) config('filament-map-picker.default_location.lng', 46.6753);
        $this->defaultZoom = (int) config('filament-map-picker.default_zoom', 10);

        $this->height = (int) config('filament-map-picker.height', 400);

        $this->draggable = (bool) config('filament-map-picker.marker.draggable', true);
        $this->searchable = (bool) config('filament-map-picker.search.enabled', true);
        $this->searchIsCollapsible = (bool) config('filament-map-picker.search.collapsible', false);

        $this->tileProvider = (string) config('filament-map-picker.tile_provider', TileProvider::OpenStreetMap->value);
        $this->autoDarkMode = (bool) config('filament-map-picker.auto_dark_mode', true);

        $this->showMyLocationControl = (bool) config('filament-map-picker.controls.my_location', true);
        $this->showFullscreenControl = (bool) config('filament-map-picker.controls.fullscreen', true);
        $this->showResetControl = (bool) config('filament-map-picker.controls.reset', true);

        $this->controlPosition = (string) config('filament-map-picker.control_position', ControlPosition::BottomRight->value);

        $this->markerColor = (string) config('filament-map-picker.marker.color', '#059669');
    }

    public function wireModelPrefix(string $prefix = 'data'): static
    {
        $this->wireModelPrefix = trim($prefix, '.');

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

    public function draggable(bool $draggable = true): static
    {
        $this->draggable = $draggable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function collapsibleSearch(bool $enabled = true): static
    {
        $this->searchIsCollapsible = $enabled;

        return $this;
    }

    public function latlngFields(string $latitudeField, string $longitudeField): static
    {
        $this->latitudeField = $latitudeField;
        $this->longitudeField = $longitudeField;

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

    public function controlPosition(string | ControlPosition $position): static
    {
        $this->controlPosition = $position instanceof ControlPosition ? $position->value : $position;

        return $this;
    }

    public function showMyLocationControl(bool $enabled = true): static
    {
        $this->showMyLocationControl = $enabled;

        return $this;
    }

    public function showFullscreenControl(bool $enabled = true): static
    {
        $this->showFullscreenControl = $enabled;

        return $this;
    }

    public function showResetControl(bool $enabled = true): static
    {
        $this->showResetControl = $enabled;

        return $this;
    }

    public function markerColor(string $color): static
    {
        $this->markerColor = $color;

        return $this;
    }

    public function getWireModelPrefix(): string
    {
        return $this->wireModelPrefix;
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

    public function isDraggable(): bool
    {
        return $this->draggable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isSearchCollapsible(): bool
    {
        return $this->searchIsCollapsible;
    }

    public function getLatitudeField(): string
    {
        return $this->latitudeField;
    }

    public function getLongitudeField(): string
    {
        return $this->longitudeField;
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

    public function getControlPosition(): string
    {
        return $this->controlPosition;
    }

    public function shouldShowMyLocationControl(): bool
    {
        return $this->showMyLocationControl;
    }

    public function shouldShowFullscreenControl(): bool
    {
        return $this->showFullscreenControl;
    }

    public function shouldShowResetControl(): bool
    {
        return $this->showResetControl;
    }

    public function getMarkerColor(): string
    {
        return $this->markerColor;
    }
}
