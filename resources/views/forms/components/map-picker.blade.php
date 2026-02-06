@php
    $latField = $getLatitudeField();
    $lngField = $getLongitudeField();
    $mapId = 'filament-map-picker-' . Str::random(8);

    $tileLightUrl = $getTileLightUrl();
    $tileDarkUrl = $getTileDarkUrl();
    $tileAttribution = $getTileAttribution();
    $markerColor = $getMarkerColor();

    $iconSearchSvg = view('filament-map-picker::icons.search')->render();
    $iconMyLocationSvg = view('filament-map-picker::icons.my-location')->render();
    $iconZoomInSvg = view('filament-map-picker::icons.zoom-in')->render();
    $iconZoomOutSvg = view('filament-map-picker::icons.zoom-out')->render();
    $iconResetSvg = view('filament-map-picker::icons.reset')->render();
    $iconFullscreenSvg = view('filament-map-picker::icons.fullscreen')->render();

    $searchConfig = config('filament-map-picker.search', []);
    $nominatim = $searchConfig['nominatim'] ?? [];
    $searchUrl = $nominatim['search_url'] ?? 'https://nominatim.openstreetmap.org/search';
    $reverseUrl = $nominatim['reverse_url'] ?? 'https://nominatim.openstreetmap.org/reverse';
    $searchMinLength = (int) ($searchConfig['min_length'] ?? 3);
    $searchLimit = (int) ($searchConfig['limit'] ?? 5);
    $searchThrottleMs = (int) ($searchConfig['throttle_ms'] ?? 1100);
    $nominatimEmail = $nominatim['email'] ?? null;
@endphp

@once('filament-map-picker-leaflet-assets')
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" data-navigate-track />
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" data-navigate-track></script>
    @endpush
@endonce

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @once('filament-map-picker-styles')
        <style>
            .filament-map-picker-wrapper {
                position: relative;
                width: 100%;
            }

            .filament-map-picker-container {
                position: relative;
                width: 100%;
                border-radius: 0.5rem;
                overflow: hidden;
                border: 1px solid #d1d5db;
            }

            .dark .filament-map-picker-container {
                border-color: #4b5563;
            }

            .filament-map-picker-map {
                width: 100%;
                height: 100%;
                z-index: 1;
            }

            .filament-map-picker-search {
                position: absolute;
                top: 12px;
                left: 12px;
                right: 12px;
                z-index: 1000;
            }

            .filament-map-picker-search-input {
                width: 100%;
                padding: 10px 46px 10px 40px;
                font-size: 14px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                background: white;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                text-align: start;
            }

            .dark .filament-map-picker-search-input {
                background: #1f2937;
                border-color: #4b5563;
                color: white;
            }

            .filament-map-picker-search-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 10px;
                border: 1px solid #d1d5db;
                background: white;
                color: #374151;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                cursor: pointer;
                transition: all 0.15s;
            }

            .filament-map-picker-search-toggle:hover {
                border-color: #059669;
                color: #059669;
            }

            .dark .filament-map-picker-search-toggle {
                background: #1f2937;
                border-color: #4b5563;
                color: #d1d5db;
            }

            .dark .filament-map-picker-search-toggle:hover {
                border-color: #34d399;
                color: #34d399;
            }

            .filament-map-picker-search-input:focus {
                outline: none;
                border-color: #059669;
                box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
            }

            .filament-map-picker-search-icon {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #9ca3af;
                pointer-events: none;
            }

            .filament-map-picker-actions {
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                display: flex;
                gap: 4px;
            }

            .filament-map-picker-btn {
                padding: 6px;
                border-radius: 6px;
                border: none;
                background: transparent;
                cursor: pointer;
                color: #6b7280;
                transition: all 0.15s;
            }

            .filament-map-picker-btn:hover {
                background: #f3f4f6;
                color: #059669;
            }

            .dark .filament-map-picker-btn:hover {
                background: #374151;
            }

            .filament-map-picker-btn-danger:hover {
                background: #fef2f2;
                color: #dc2626;
            }

            .dark .filament-map-picker-btn-danger:hover {
                background: rgba(220, 38, 38, 0.1);
            }

            .filament-map-picker-suggestions {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                margin-top: 4px;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                max-height: 240px;
                overflow-y: auto;
                z-index: 1001;
            }

            .dark .filament-map-picker-suggestions {
                background: #1f2937;
                border-color: #374151;
            }

            .filament-map-picker-suggestion {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 12px;
                border: none;
                background: none;
                width: 100%;
                text-align: left;
                cursor: pointer;
                border-bottom: 1px solid #f3f4f6;
            }

            .dark .filament-map-picker-suggestion {
                border-bottom-color: #374151;
            }

            .filament-map-picker-suggestion:last-child {
                border-bottom: none;
            }

            .filament-map-picker-suggestion:hover {
                background: #f9fafb;
            }

            .dark .filament-map-picker-suggestion:hover {
                background: #374151;
            }

            .filament-map-picker-suggestion-icon {
                color: #9ca3af;
                flex-shrink: 0;
                margin-top: 2px;
            }

            .filament-map-picker-suggestion-name {
                font-weight: 500;
                color: #111827;
                margin-bottom: 2px;
            }

            .dark .filament-map-picker-suggestion-name {
                color: #f9fafb;
            }

            .filament-map-picker-suggestion-address {
                font-size: 12px;
                color: #6b7280;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .filament-map-picker-loading {
                padding: 16px;
                text-align: center;
                color: #6b7280;
            }

            .filament-map-picker-coords {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 8px;
                padding: 0 4px;
                font-size: 13px;
                color: #6b7280;
            }

            .filament-map-picker-location-name {
                color: #374151;
                max-width: 50%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .dark .filament-map-picker-location-name {
                color: #d1d5db;
            }

            .filament-map-picker-custom-marker {
                background: none !important;
                border: none !important;
            }

            .leaflet-container {
                z-index: 1 !important;
                font-family: inherit;
            }

            .filament-map-picker-action-control.leaflet-bar {
                border: 1px solid #d1d5db;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                background: white;
            }

            .dark .filament-map-picker-action-control.leaflet-bar {
                border-color: #4b5563;
                background: #1f2937;
            }

            .filament-map-picker-action-control.leaflet-bar a {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                line-height: 40px;
                border: none;
                margin: 0;
                color: #374151;
                background: transparent;
                transition: all 0.15s;
            }

            .dark .filament-map-picker-action-control.leaflet-bar a {
                color: #d1d5db;
            }

            .filament-map-picker-action-control.leaflet-bar a + a {
                border-top: 1px solid #e5e7eb;
            }

            .dark .filament-map-picker-action-control.leaflet-bar a + a {
                border-top-color: #374151;
            }

            .filament-map-picker-action-control.leaflet-bar a:hover {
                background: #f3f4f6;
                color: #059669;
            }

            .dark .filament-map-picker-action-control.leaflet-bar a:hover {
                background: #374151;
                color: #34d399;
            }

            .filament-map-picker-action-control.leaflet-bar a:focus-visible {
                outline: 2px solid rgba(5, 150, 105, 0.35);
                outline-offset: -2px;
            }

            .filament-map-picker-action-control.leaflet-bar svg {
                display: block;
                width: 20px;
                height: 20px;
            }
        </style>
    @endonce

    <div
        wire:ignore
        x-data="filamentMapPicker_{{ Str::replace('-', '_', $mapId) }}()"
        class="filament-map-picker-wrapper"
    >
        <div class="filament-map-picker-container" x-ref="container" style="height: {{ $getHeight() }}px;">
            <div id="{{ $mapId }}" class="filament-map-picker-map"></div>

            @if($isSearchable())
                <div class="filament-map-picker-search" x-show="searchIsCollapsible && !isSearchOpen" x-transition>
                    <button
                        type="button"
                        class="filament-map-picker-search-toggle"
                        x-on:click="openSearch()"
                        title="{{ __('Search') }}"
                    >
                        @include('filament-map-picker::icons.search')
                    </button>
                </div>

                <div class="filament-map-picker-search" x-show="!searchIsCollapsible || isSearchOpen" x-transition>
                    <div style="position: relative;">
                        <div class="filament-map-picker-search-icon">
                            @include('filament-map-picker::icons.search')
                        </div>

                        <input
                            type="text"
                            dir="auto"
                            x-ref="searchInput"
                            x-model="searchQuery"
                            x-on:input.debounce.400ms="searchPlaces()"
                            x-on:focus="showSuggestions = suggestions.length > 0"
                            x-on:keydown.enter.prevent="selectFirstSuggestion()"
                            x-on:keydown.escape="showSuggestions = false; collapseSearchIfEmpty()"
                            x-on:blur="handleSearchBlur()"
                            placeholder="{{ __('Search for a location...') }}"
                            class="filament-map-picker-search-input"
                            autocomplete="off"
                        />

                        <div class="filament-map-picker-actions">
                            <button type="button" x-show="hasLocation()" x-on:click="clearLocation()" class="filament-map-picker-btn filament-map-picker-btn-danger" title="{{ __('Clear') }}">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div x-show="showSuggestions && suggestions.length > 0" x-on:click.away="showSuggestions = false" x-transition class="filament-map-picker-suggestions">
                            <template x-for="(s, i) in suggestions" :key="i">
                                <button type="button" x-on:click="selectSuggestion(s)" class="filament-map-picker-suggestion">
                                    <svg class="filament-map-picker-suggestion-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="filament-map-picker-suggestion-name" x-text="s.name"></div>
                                        <div class="filament-map-picker-suggestion-address" x-text="s.address"></div>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <div x-show="isSearching" class="filament-map-picker-suggestions filament-map-picker-loading">
                            <svg class="animate-spin" style="margin: 0 auto 8px; display: block;" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity: 0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path style="opacity: 0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Searching...') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="filament-map-picker-coords">
            <div style="display: flex; align-items: center; gap: 6px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span x-text="getDisplayCoords()"></span>
            </div>
            <div x-show="locationName" class="filament-map-picker-location-name" x-text="locationName"></div>
        </div>
    </div>

    <script>
        function filamentMapPicker_{{ Str::replace('-', '_', $mapId) }}() {
            return {
                map: null,
                marker: null,
                tileLayer: null,
                currentLat: null,
                currentLng: null,
                searchQuery: '',
                suggestions: [],
                showSuggestions: false,
                isSearching: false,
                locationName: '',

                defaultLat: {{ $getDefaultLatitude() }},
                defaultLng: {{ $getDefaultLongitude() }},
                defaultZoom: {{ $getDefaultZoom() }},

                draggable: {{ $isDraggable() ? 'true' : 'false' }},
                latField: '{{ $latField }}',
                lngField: '{{ $lngField }}',

                tileLightUrl: @js($tileLightUrl),
                tileDarkUrl: @js($tileDarkUrl),
                tileAttribution: @js($tileAttribution),
                autoDarkMode: {{ $isAutoDarkMode() ? 'true' : 'false' }},

                showMyLocationControl: {{ $shouldShowMyLocationControl() ? 'true' : 'false' }},
                showFullscreenControl: {{ $shouldShowFullscreenControl() ? 'true' : 'false' }},
                showResetControl: {{ $shouldShowResetControl() ? 'true' : 'false' }},
                controlPosition: @js($getControlPosition()),

                wireModelPrefix: @js($getWireModelPrefix()),

                searchIsCollapsible: {{ $isSearchCollapsible() ? 'true' : 'false' }},
                isSearchOpen: false,

                searchUrl: @js($searchUrl),
                reverseUrl: @js($reverseUrl),
                searchMinLength: {{ $searchMinLength }},
                searchLimit: {{ $searchLimit }},
                searchThrottleMs: {{ $searchThrottleMs }},
                nominatimEmail: @js($nominatimEmail),

                isDestroyed: false,
                themeObserver: null,
                pendingSearchTimer: null,
                pendingReverseTimer: null,
                nextSearchAt: 0,
                nextReverseAt: 0,
                fullscreenChangeHandler: null,
                navigatingHandler: null,

                init() {
                    this.navigatingHandler = () => this.destroy();
                    document.addEventListener('livewire:navigating', this.navigatingHandler, { once: true });

                    this.isSearchOpen = !this.searchIsCollapsible;

                    this.$nextTick(() => this.initMap());
                },

                getLat() {
                    return this.currentLat;
                },

                getLng() {
                    return this.currentLng;
                },

                hasLocation() {
                    return this.currentLat !== null && this.currentLng !== null;
                },

                getDisplayCoords() {
                    const lat = this.getLat();
                    const lng = this.getLng();
                    if (lat && lng) {
                        return parseFloat(lat).toFixed(6) + ', ' + parseFloat(lng).toFixed(6);
                    }
                    return @js(__('Click on map to select location'));
                },

                initMap() {
                    if (this.isDestroyed) return;

                    if (typeof window.L === 'undefined') {
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }

                    const existingLat = this.$wire.get(this.wireModelPrefix + '.' + this.latField);
                    const existingLng = this.$wire.get(this.wireModelPrefix + '.' + this.lngField);

                    this.currentLat = (existingLat !== null && existingLat !== undefined && existingLat !== '') ? parseFloat(existingLat) : null;
                    this.currentLng = (existingLng !== null && existingLng !== undefined && existingLng !== '') ? parseFloat(existingLng) : null;

                    const lat = this.currentLat ?? this.defaultLat;
                    const lng = this.currentLng ?? this.defaultLng;
                    const zoom = this.currentLat !== null ? 15 : this.defaultZoom;

                    this.map = L.map(@js($mapId), { zoomControl: false }).setView([lat, lng], zoom);

                    this.tileLayer = L.tileLayer(this.getActiveTileUrl(), {
                        attribution: this.tileAttribution,
                        maxZoom: 19
                    }).addTo(this.map);

                    if (this.autoDarkMode) {
                        this.observeThemeChanges();
                    }

                    if (this.currentLat !== null && this.currentLng !== null) {
                        this.addMarker(lat, lng);
                        this.reverseGeocode(lat, lng);
                    }

                    this.map.on('click', (e) => {
                        this.setLocation(e.latlng.lat, e.latlng.lng);
                        this.reverseGeocode(e.latlng.lat, e.latlng.lng);
                    });

                    this.addActionControls();

                    this.setupFullscreenChangeListener();

                    setTimeout(() => this.map.invalidateSize(), 300);
                },

                getActiveTileUrl() {
                    const isDark = document.documentElement.classList.contains('dark');
                    return (this.autoDarkMode && isDark) ? this.tileDarkUrl : this.tileLightUrl;
                },

                observeThemeChanges() {
                    const updateTiles = () => {
                        if (!this.tileLayer) return;
                        const url = this.getActiveTileUrl();
                        this.tileLayer.setUrl(url);
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

                setupFullscreenChangeListener() {
                    const el = this.$refs.container;
                    if (!el) return;

                    this.fullscreenChangeHandler = () => {
                        setTimeout(() => this.map?.invalidateSize?.(), 150);
                    };

                    el.addEventListener('fullscreenchange', this.fullscreenChangeHandler);
                    document.addEventListener('fullscreenchange', this.fullscreenChangeHandler);
                },

                addActionControls() {
                    const position = this.controlPosition || 'bottomright';

                    const ActionControl = L.Control.extend({
                        options: { position },
                        onAdd: () => {
                            const container = L.DomUtil.create('div', 'leaflet-bar filament-map-picker-action-control');

                            const makeButton = (title, svg, onClick) => {
                                const a = L.DomUtil.create('a', '', container);
                                a.href = '#';
                                a.title = title;
                                a.setAttribute('aria-label', title);
                                a.setAttribute('role', 'button');
                                a.innerHTML = svg;
                                L.DomEvent.disableClickPropagation(a);
                                L.DomEvent.on(a, 'click', L.DomEvent.stop);
                                L.DomEvent.on(a, 'click', () => onClick());
                                return a;
                            };

                            if (this.showMyLocationControl) {
                                makeButton(
                                    @js(__('My Location')),
                                    @js($iconMyLocationSvg),
                                    () => this.getMyLocation()
                                );
                            }

                            makeButton(
                                @js(__('Zoom in')),
                                @js($iconZoomInSvg),
                                () => this.map?.zoomIn?.()
                            );

                            makeButton(
                                @js(__('Zoom out')),
                                @js($iconZoomOutSvg),
                                () => this.map?.zoomOut?.()
                            );

                            if (this.showResetControl) {
                                makeButton(
                                    @js(__('Reset')),
                                    @js($iconResetSvg),
                                    () => this.resetView()
                                );
                            }

                            if (this.showFullscreenControl) {
                                makeButton(
                                    @js(__('Fullscreen')),
                                    @js($iconFullscreenSvg),
                                    () => this.toggleFullscreen()
                                );
                            }

                            return container;
                        },
                    });

                    this.map.addControl(new ActionControl());
                },

                toggleFullscreen() {
                    const el = this.$refs.container;
                    if (!el) return;

                    if (document.fullscreenElement) {
                        document.exitFullscreen?.();
                        return;
                    }

                    el.requestFullscreen?.();
                },

                openSearch() {
                    this.isSearchOpen = true;
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus?.();
                    });
                },

                collapseSearchIfEmpty() {
                    if (!this.searchIsCollapsible) return;
                    if ((this.searchQuery || '').trim().length > 0) return;
                    this.isSearchOpen = false;
                    this.showSuggestions = false;
                    this.suggestions = [];
                },

                handleSearchBlur() {
                    if (!this.searchIsCollapsible) return;

                    setTimeout(() => {
                        if (document.activeElement === this.$refs.searchInput) return;
                        if (this.showSuggestions) return;
                        this.collapseSearchIfEmpty();
                    }, 150);
                },

                resetView() {
                    const existingLat = this.getLat();
                    const existingLng = this.getLng();
                    if (existingLat && existingLng) {
                        this.map.setView([parseFloat(existingLat), parseFloat(existingLng)], 15);
                        return;
                    }
                    this.map.setView([this.defaultLat, this.defaultLng], this.defaultZoom);
                },

                createMarkerIcon() {
                    const fill = @js($markerColor);

                    return L.divIcon({
                        html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${fill}" width="36" height="36" style="filter: drop-shadow(0 2px 3px rgba(0,0,0,0.3));"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742zM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" clip-rule="evenodd" /></svg>`,
                        className: 'filament-map-picker-custom-marker',
                        iconSize: [36, 36],
                        iconAnchor: [18, 36]
                    });
                },

                addMarker(lat, lng) {
                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    } else {
                        this.marker = L.marker([lat, lng], {
                            draggable: this.draggable,
                            icon: this.createMarkerIcon()
                        }).addTo(this.map);

                        if (this.draggable) {
                            this.marker.on('dragend', (e) => {
                                const pos = e.target.getLatLng();
                                this.updateCoordinates(pos.lat, pos.lng);
                                this.reverseGeocode(pos.lat, pos.lng);
                            });
                        }
                    }
                },

                setLocation(lat, lng) {
                    this.addMarker(lat, lng);
                    this.updateCoordinates(lat, lng);
                },

                updateCoordinates(lat, lng) {
                    this.currentLat = lat;
                    this.currentLng = lng;
                    this.$wire.set(this.wireModelPrefix + '.' + this.latField, parseFloat(lat.toFixed(8)));
                    this.$wire.set(this.wireModelPrefix + '.' + this.lngField, parseFloat(lng.toFixed(8)));
                },

                clearLocation() {
                    this.currentLat = null;
                    this.currentLng = null;
                    this.$wire.set(this.wireModelPrefix + '.' + this.latField, null);
                    this.$wire.set(this.wireModelPrefix + '.' + this.lngField, null);

                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                        this.marker = null;
                    }

                    this.locationName = '';
                    this.searchQuery = '';
                    this.map.setView([this.defaultLat, this.defaultLng], this.defaultZoom);

                    this.collapseSearchIfEmpty();
                },

                async getMyLocation() {
                    if (!navigator.geolocation) {
                        alert(@js(__('Geolocation is not supported')));
                        return;
                    }

                    const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);
                    if (!window.isSecureContext && !isLocalhost) {
                        alert(@js(__('Geolocation requires HTTPS')));
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            this.setLocation(lat, lng);
                            this.map.setView([lat, lng], 16);
                            this.reverseGeocode(lat, lng);
                        },
                        (err) => {
                            const base = @js(__('Unable to get location'));

                            const code = err?.code;
                            const reason = err?.message ? `: ${err.message}` : '';

                            const hint = (() => {
                                switch (code) {
                                    case 1:
                                        return @js(__('Permission denied. Please allow location access in your browser.'));
                                    case 2:
                                        return @js(__('Position unavailable. Check GPS and network settings.'));
                                    case 3:
                                        return @js(__('Request timed out. Try again.'));
                                    default:
                                        return '';
                                }
                            })();

                            alert(hint ? `${base}${reason}\n${hint}` : `${base}${reason}`);
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                },

                async searchPlaces() {
                    if (this.searchQuery.length < this.searchMinLength) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        return;
                    }

                    if (this.pendingSearchTimer) {
                        clearTimeout(this.pendingSearchTimer);
                        this.pendingSearchTimer = null;
                    }

                    const now = Date.now();
                    if (now < this.nextSearchAt) {
                        this.pendingSearchTimer = setTimeout(() => this.searchPlaces(), this.nextSearchAt - now);
                        return;
                    }

                    this.nextSearchAt = now + this.searchThrottleMs;

                    this.isSearching = true;

                    try {
                        const lang = document.documentElement.lang || 'en';
                        const url = new URL(this.searchUrl);
                        url.searchParams.set('format', 'json');
                        url.searchParams.set('q', this.searchQuery);
                        url.searchParams.set('limit', String(this.searchLimit));
                        url.searchParams.set('addressdetails', '1');
                        url.searchParams.set('accept-language', lang);
                        if (this.nominatimEmail) url.searchParams.set('email', this.nominatimEmail);

                        const res = await fetch(url.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });

                        const data = await res.json();
                        this.suggestions = (data || []).map(item => ({
                            name: item.name || (item.display_name ? item.display_name.split(',')[0] : ''),
                            address: item.display_name || '',
                            lat: parseFloat(item.lat),
                            lng: parseFloat(item.lon)
                        }));
                        this.showSuggestions = this.suggestions.length > 0;
                    } catch (e) {
                        this.suggestions = [];
                    }

                    this.isSearching = false;
                },

                selectSuggestion(s) {
                    this.setLocation(s.lat, s.lng);
                    this.map.setView([s.lat, s.lng], 16);
                    this.locationName = s.name;
                    this.searchQuery = s.name;
                    this.showSuggestions = false;
                },

                selectFirstSuggestion() {
                    if (this.suggestions.length > 0) this.selectSuggestion(this.suggestions[0]);
                },

                async reverseGeocode(lat, lng) {
                    if (this.pendingReverseTimer) {
                        clearTimeout(this.pendingReverseTimer);
                        this.pendingReverseTimer = null;
                    }

                    const now = Date.now();
                    if (now < this.nextReverseAt) {
                        this.pendingReverseTimer = setTimeout(() => this.reverseGeocode(lat, lng), this.nextReverseAt - now);
                        return;
                    }

                    this.nextReverseAt = now + this.searchThrottleMs;

                    try {
                        const lang = document.documentElement.lang || 'en';
                        const url = new URL(this.reverseUrl);
                        url.searchParams.set('format', 'json');
                        url.searchParams.set('lat', String(lat));
                        url.searchParams.set('lon', String(lng));
                        url.searchParams.set('accept-language', lang);
                        if (this.nominatimEmail) url.searchParams.set('email', this.nominatimEmail);

                        const res = await fetch(url.toString(), {
                            headers: { 'Accept': 'application/json' }
                        });

                        const data = await res.json();
                        if (data?.display_name) {
                            this.locationName = data.name || data.display_name.split(',')[0];
                        }
                    } catch (e) {}
                },

                destroy() {
                    if (this.isDestroyed) return;
                    this.isDestroyed = true;

                    if (this.pendingSearchTimer) {
                        clearTimeout(this.pendingSearchTimer);
                        this.pendingSearchTimer = null;
                    }

                    if (this.pendingReverseTimer) {
                        clearTimeout(this.pendingReverseTimer);
                        this.pendingReverseTimer = null;
                    }

                    this.themeObserver?.disconnect?.();
                    this.themeObserver = null;

                    const el = this.$refs.container;
                    if (el && this.fullscreenChangeHandler) {
                        el.removeEventListener('fullscreenchange', this.fullscreenChangeHandler);
                    }
                    if (this.fullscreenChangeHandler) {
                        document.removeEventListener('fullscreenchange', this.fullscreenChangeHandler);
                    }
                    this.fullscreenChangeHandler = null;

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
</x-dynamic-component>
