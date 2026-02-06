@php
    $state = $getState();
    $lat = data_get($state, $getLatitudeField()) ?? data_get($getRecord(), $getLatitudeField());
    $lng = data_get($state, $getLongitudeField()) ?? data_get($getRecord(), $getLongitudeField());

    $mapId = 'filament-map-view-' . Str::random(8);
    $tileLightUrl = $getTileLightUrl();
    $tileDarkUrl = $getTileDarkUrl();
    $tileAttribution = $getTileAttribution();

    $searchConfig = config('filament-map-picker.search', []);
    $searchThrottleMs = (int) ($searchConfig['throttle_ms'] ?? 1100);
@endphp

@once('filament-map-picker-leaflet-assets')
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" data-navigate-track />
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" data-navigate-track></script>
    @endpush
@endonce

<div
    x-data="filamentMapView_{{ Str::replace('-', '_', $mapId) }}()"
    style="width: 100%;"
>
    <div
        id="{{ $mapId }}"
        style="height: {{ $getHeight() }}px; border-radius: 0.5rem; overflow: hidden; border: 1px solid #d1d5db;"
    ></div>

    <div style="margin-top: 8px; font-size: 13px; color: #6b7280;">
        @if (filled($lat) && filled($lng))
            {{ number_format((float) $lat, 6) }}, {{ number_format((float) $lng, 6) }}
        @else
            {{ __('No location set') }}
        @endif
    </div>

    <script>
        function filamentMapView_{{ Str::replace('-', '_', $mapId) }}() {
            return {
                map: null,
                tileLayer: null,
                marker: null,
                themeObserver: null,
                fullscreenChangeHandler: null,
                navigatingHandler: null,
                isDestroyed: false,

                lat: @js(filled($lat) ? (float) $lat : null),
                lng: @js(filled($lng) ? (float) $lng : null),

                defaultLat: {{ $getDefaultLatitude() }},
                defaultLng: {{ $getDefaultLongitude() }},
                defaultZoom: {{ $getDefaultZoom() }},

                tileLightUrl: @js($tileLightUrl),
                tileDarkUrl: @js($tileDarkUrl),
                tileAttribution: @js($tileAttribution),
                autoDarkMode: {{ $isAutoDarkMode() ? 'true' : 'false' }},
                throttleMs: {{ $searchThrottleMs }},

                init() {
                    this.navigatingHandler = () => this.destroy();
                    document.addEventListener('livewire:navigating', this.navigatingHandler, { once: true });

                    this.$nextTick(() => this.initMap());
                },

                initMap() {
                    if (this.isDestroyed) return;

                    if (typeof window.L === 'undefined') {
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }

                    const lat = this.lat ?? this.defaultLat;
                    const lng = this.lng ?? this.defaultLng;
                    const zoom = (this.lat !== null && this.lng !== null) ? 15 : this.defaultZoom;

                    this.map = L.map(@js($mapId), { zoomControl: true, dragging: false, scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false, keyboard: false, tap: false })
                        .setView([lat, lng], zoom);

                    this.tileLayer = L.tileLayer(this.getActiveTileUrl(), {
                        attribution: this.tileAttribution,
                        maxZoom: 19
                    }).addTo(this.map);

                    if (this.autoDarkMode) {
                        this.observeThemeChanges();
                    }

                    if (this.lat !== null && this.lng !== null) {
                        this.marker = L.marker([this.lat, this.lng]).addTo(this.map);
                    }
                },

                getActiveTileUrl() {
                    const isDark = document.documentElement.classList.contains('dark');
                    return (this.autoDarkMode && isDark) ? this.tileDarkUrl : this.tileLightUrl;
                },

                observeThemeChanges() {
                    const updateTiles = () => {
                        if (!this.tileLayer) return;
                        this.tileLayer.setUrl(this.getActiveTileUrl());
                    };

                    this.themeObserver?.disconnect?.();

                    this.themeObserver = new MutationObserver((mutations) => {
                        for (const mutation of mutations) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                updateTiles();
                            }
                        }
                    });

                    this.themeObserver.observe(document.documentElement, { attributes: true });
                },

                destroy() {
                    if (this.isDestroyed) return;
                    this.isDestroyed = true;

                    this.themeObserver?.disconnect?.();
                    this.themeObserver = null;

                    try {
                        this.map?.off?.();
                        this.map?.remove?.();
                    } catch (e) {}

                    this.map = null;
                    this.marker = null;
                    this.tileLayer = null;
                },
            };
        }
    </script>
</div>
