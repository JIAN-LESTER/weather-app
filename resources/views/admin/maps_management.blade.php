@extends('layouts.app')

@section('title', 'Maps')
@section('header', 'Maps')

{{-- These sections make the layout fullscreen for maps --}}
@section('fullscreen', 'relative')
@section('header-class', 'absolute top-0 left-0 right-0 z-30')
@section('main-class', 'h-full pt-14 sm:pt-16 md:pt-20 relative')

@section('content')
    <div class="h-full relative">
        <!-- Map Loading Indicator -->
        <div id="mapLoader" class="absolute inset-0 bg-gray-50 flex items-center justify-center z-20">
            <div class="flex items-center gap-2 sm:gap-3 px-4">
                <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-600 font-medium text-sm sm:text-base">Loading map...</span>
            </div>
        </div>

        <!-- Map - Full height -->
        <div id="map" class="h-full w-full z-0"></div>

        <!-- Weather Panel - Mobile Optimized -->
        <div id="weatherPanel"
            class="fixed inset-x-2 bottom-2 sm:absolute sm:top-4 sm:left-4 sm:right-auto sm:bottom-auto bg-white/95 backdrop-blur-sm rounded-lg shadow-xl w-auto sm:w-full sm:max-w-md z-20 hidden max-h-[70vh] sm:max-h-[80vh] overflow-y-auto">
        </div>

        <!-- All Controls - Mobile Optimized -->
        <div class="dark:text-white dark:bg-gray-800 bg-white text-gray-800 absolute top-8 right-2 sm:bottom-4 sm:top-auto sm:right-4 z-10 max-w-[calc(100vw-1rem)] sm:max-w-xs">
            <div class=" backdrop-blur-sm rounded-lg shadow-lg p-2 sm:p-3 space-y-2 sm:space-y-3">
                <!-- Map Style -->
                <select id="mapStyle"
                    class="w-full border border-gray-300 rounded-md px-2 py-1.5 sm:px-3 sm:py-2 bg-white dark:bg-gray-600 dark:text-white text-gray-800 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <option value="landscape" selected>Landscape</option>
                    <option value="outdoors">Outdoors</option>
                    <option value="transport">Transport</option>
                    <option value="transportDark">Transport Dark</option>
                    <option value="spinalMap">Spinal Map</option>
                    <option value="pioneer">Pioneer</option>
                    <option value="mobileAtlas">Mobile Atlas</option>
                    <option value="neighbourhood">Neighbourhood</option>
                </select>

                <!-- Weather Layers -->
                <div class="flex flex-col gap-1.5 sm:gap-2">
                    <div class="text-xs font-medium text-gray-500">Weather Layers</div>
                    
                    <!-- Temperature -->
                    <label class="flex items-center justify-between gap-2 cursor-pointer group" title="Temperature">
                        <span class="text-xs sm:text-sm">🌡️ <span class="hidden xs:inline">Temp</span></span>
                        <input type="checkbox" class="sr-only peer weather-switch" data-layer="temp">
                        <div class="w-9 h-5 sm:w-10 bg-gray-200 rounded-full peer peer-checked:after:translate-x-4 sm:peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 relative"></div>
                    </label>

                    <!-- Precipitation -->
                    <label class="flex items-center justify-between gap-2 cursor-pointer group" title="Rain & Clouds">
                        <span class="text-xs sm:text-sm">🌧️ <span class="hidden xs:inline">Precip</span></span>
                        <input type="checkbox" class="sr-only peer weather-switch" data-layer="precipitation">
                        <div class="w-9 h-5 sm:w-10 bg-gray-200 rounded-full peer peer-checked:after:translate-x-4 sm:peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 relative"></div>
                    </label>

                    <!-- Wind -->
                    <label class="flex items-center justify-between gap-2 cursor-pointer group" title="Wind">
                        <span class="text-xs sm:text-sm">💨 <span class="hidden xs:inline">Wind</span></span>
                        <input type="checkbox" class="sr-only peer weather-switch" data-layer="wind">
                        <div class="w-9 h-5 sm:w-10 bg-gray-200 rounded-full peer peer-checked:after:translate-x-4 sm:peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 relative"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Map Legend - Mobile Optimized -->
        <div class="absolute bottom-2 left-2 sm:bottom-4 sm:left-4 bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-white backdrop-blur-sm rounded-lg shadow-lg p-2 sm:p-4 max-w-[calc(100vw-140px)] sm:max-w-lg z-10">
            <div id="basicLegend" class="flex flex-col xs:flex-row flex-wrap items-start xs:items-center gap-2 xs:gap-4 text-xs sm:text-sm">
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="w-5 h-3 sm:w-6 sm:h-4 bg-gradient-to-r dark:text-white from-blue-400 via-green-400 via-yellow-400 to-red-400 rounded opacity-70"></div>
                    <span class="">Temp</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="w-5 h-3 sm:w-6 sm:h-4 bg-gradient-to-r dark:text-white from-transparent via-blue-300 to-purple-600 rounded opacity-60"></div>
                    <span class="">Precip</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="w-5 h-3 sm:w-6 sm:h-4 bg-gradient-to-r dark:text-white from-green-300 to-red-400 rounded opacity-60"></div>
                    <span class="">Wind</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        /* Responsive breakpoint for very small screens */
        @media (min-width: 375px) {
            .xs\:inline {
                display: inline;
            }
            .xs\:flex-row {
                flex-direction: row;
            }
            .xs\:items-center {
                align-items: center;
            }
        }

        /* Prevent horizontal scroll on mobile */
        body {
            overflow-x: hidden;
        }

        .leaflet-control-zoom {
            border-radius: 6px !important;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }

        .leaflet-control-zoom a {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            color: #374151 !important;
            font-weight: 600;
            transition: all 0.2s ease;
            width: 26px !important;
            height: 26px !important;
            line-height: 26px !important;
        }

        @media (min-width: 640px) {
            .leaflet-control-zoom {
                border-radius: 8px !important;
            }
            .leaflet-control-zoom a {
                width: 30px !important;
                height: 30px !important;
                line-height: 30px !important;
            }
        }

        .leaflet-control-zoom a:hover {
            background: #f3f4f6 !important;
            transform: scale(1.05);
        }

        .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(229, 231, 235, 0.8);
            padding: 0;
            max-width: calc(100vw - 3rem) !important;
        }

        @media (min-width: 640px) {
            .leaflet-popup-content-wrapper {
                border-radius: 16px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                max-width: 400px !important;
            }
        }

        .leaflet-popup-tip {
            background: rgba(255, 255, 255, 0.98);
        }

        .leaflet-popup-content {
            margin: 0 !important;
            width: auto !important;
            max-width: 100% !important;
        }

        .weather-popup .leaflet-popup-content,
        .alert-popup .leaflet-popup-content {
            margin: 0 !important;
            width: auto !important;
        }

        /* Prevent popup from being too wide on mobile */
        @media (max-width: 639px) {
            .leaflet-popup {
                max-width: calc(100vw - 2rem) !important;
            }
            
            .leaflet-popup-content-wrapper {
                max-width: calc(100vw - 3rem) !important;
            }
        }

        .weather-switch:checked+div {
            animation: switchOn 0.3s ease-in-out;
        }

        @keyframes switchOn {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .switch-loading {
            position: relative;
            overflow: hidden;
        }

        .switch-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
            animation: loading-shimmer 1s infinite;
        }

        @keyframes loading-shimmer {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }

        .alert-marker {
            background: transparent !important;
            border: none !important;
        }
        
        .alert-popup .leaflet-popup-content-wrapper {
            padding: 0;
            overflow: hidden;
        }
        
        .alert-popup .leaflet-popup-content {
            margin: 0 !important;
            width: auto !important;
        }
        
        @keyframes pulse-alert {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .animate-ping {
            animation: pulse-alert 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* Mobile-specific adjustments */
        @media (max-width: 639px) {
          
            .leaflet-control-zoom {
                margin-top: 60px !important;
                margin-right: 8px !important;
            }

            /* Weather panel on mobile - fixed at bottom */
            #weatherPanel {
                position: fixed !important;
                max-height: 70vh !important;
                border-radius: 12px 12px 0 0 !important;
            }

            /* Prevent body scroll when panel is open on mobile */
            body.panel-open {
                overflow: hidden;
            }
        }

        /* Smooth scrolling for weather panel */
        #weatherPanel {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }

        #weatherPanel::-webkit-scrollbar {
            width: 6px;
        }

        #weatherPanel::-webkit-scrollbar-track {
            background: transparent;
        }

        #weatherPanel::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
    </style>
<script>
        const openWeatherKey = "{{ env('OPENWEATHER_API_KEY') }}";
        const thunderforestKey = "{{ env('THUNDERFOREST_MAPS_API_KEY') }}";

        // Initialize map
        const map = L.map('map', {
            zoomControl: true,
       
            zoomSnap: 0.5,
            zoomDelta: 0.5
        }).setView([7.9, 125.1], 10);

        // Base layers
        const baseLayers = {
            landscape: L.tileLayer(`https://tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
     
                maxZoom: 22
            }),
            outdoors: L.tileLayer(`https://tile.thunderforest.com/outdoors/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
          
                maxZoom: 22
            }),
            transport: L.tileLayer(`https://tile.thunderforest.com/transport/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
           
                maxZoom: 22
            }),
            transportDark: L.tileLayer(`https://tile.thunderforest.com/transport-dark/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
   
                maxZoom: 22
            }),
            spinalMap: L.tileLayer(`https://tile.thunderforest.com/spinal-map/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
      
                maxZoom: 22
            }),
            pioneer: L.tileLayer(`https://tile.thunderforest.com/pioneer/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
   
                maxZoom: 22
            }),
            mobileAtlas: L.tileLayer(`https://tile.thunderforest.com/mobile-atlas/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
        
                maxZoom: 22
            }),
            neighbourhood: L.tileLayer(`https://tile.thunderforest.com/neighbourhood/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
              
                maxZoom: 22
            })
        };

        let currentBaseLayer = baseLayers.landscape;
        currentBaseLayer.addTo(map);

        // Weather overlay layers
        let rainviewerLayer = null;
        
        const weatherLayers = {
            temp: L.tileLayer(`https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 0.6,

                maxZoom: 18
            }),
            precipitation: null,
            wind: L.tileLayer(`https://tile.openweathermap.org/map/wind_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 0.6,
  
                maxZoom: 18
            })
        };


      // Initialize RainViewer layer
        async function initRainViewerLayer() {
            try {
                const response = await fetch('https://api.rainviewer.com/public/weather-maps.json');
                const data = await response.json();
                
                const latestTimestamp = data.radar.past[data.radar.past.length - 1].time;
                
                rainviewerLayer = L.tileLayer(
                    `https://tilecache.rainviewer.com/v2/radar/${latestTimestamp}/256/{z}/{x}/{y}/2/1_1.png`,
                    {
                        opacity: 0.6,
                        tileSize: 256,
                        zoomOffset: 0,
                        maxZoom: 18
                    }
                );
                
                weatherLayers.precipitation = rainviewerLayer;
                console.log('✅ RainViewer initialized with timestamp:', new Date(latestTimestamp * 1000).toLocaleString());
            } catch (error) {
                console.error('Error initializing RainViewer:', error);
                weatherLayers.precipitation = L.layerGroup([
                    L.tileLayer(`https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                        opacity: 0.6,
                    }),
                    L.tileLayer(`https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                        opacity: 0.4,
                    })
                ]);
            }
        }

        // Update RainViewer layer periodically
        async function updateRainViewerLayer() {
            try {
                const response = await fetch('https://api.rainviewer.com/public/weather-maps.json');
                const data = await response.json();
                const latestTimestamp = data.radar.past[data.radar.past.length - 1].time;
                
                if (rainviewerLayer) {
                    const newUrl = `https://tilecache.rainviewer.com/v2/radar/${latestTimestamp}/256/{z}/{x}/{y}/2/1_1.png`;
                    rainviewerLayer.setUrl(newUrl);
                    console.log('🔄 RainViewer updated:', new Date(latestTimestamp * 1000).toLocaleString());
                }
            } catch (error) {
                console.error('Error updating RainViewer:', error);
            }
        }

        // Initialize RainViewer and start alert monitoring
        async function initializeWeatherSystems() {
            await initRainViewerLayer();
            if (map._loaded) {
                loadAlertsForMonitoredLocations();
            }
        }

        initializeWeatherSystems();
        setInterval(updateRainViewerLayer, 600000);

        // Track active weather layers
        let activeWeatherLayers = new Set();

        // Alert markers management
        let alertMarkersLayer = L.layerGroup().addTo(map);
        let activeAlertLocations = new Map();
        let alertLayerVisible = true;
        let isLoadingAlerts = false;

        // Predefined locations to check for alerts (Soccsksargen + Bukidnon)
        const monitoredLocations = [
            { name: 'Malaybalay City', lat: 8.1542, lng: 125.1278 },
            { name: 'Valencia City', lat: 7.9064, lng: 125.0942 },
            { name: 'Maramag', lat: 7.7644, lng: 125.0058 },
            { name: 'Quezon, Bukidnon', lat: 7.7319, lng: 125.1006 },
            { name: 'Don Carlos', lat: 7.6833, lng: 125.0167 },
            { name: 'Kitaotao', lat: 7.6333, lng: 125.0167 },
            { name: 'Dangcagan', lat: 7.5833, lng: 125.0667 },
            { name: 'Kibawe', lat: 7.5667, lng: 125.0000 },
            { name: 'Damulog', lat: 7.4833, lng: 124.9333 },
            { name: 'Kadingilan', lat: 7.6000, lng: 124.9167 },
            { name: 'Kalilangan', lat: 7.7500, lng: 124.5333 },
            { name: 'Pangantucan', lat: 7.8333, lng: 124.8500 },
            { name: 'Wao', lat: 7.6500, lng: 124.7167 },
            { name: 'Baungon', lat: 8.2667, lng: 124.7333 },
            { name: 'Libona', lat: 8.3333, lng: 124.7500 },
            { name: 'Manolo Fortich', lat: 8.3667, lng: 124.8667 },
            { name: 'Sumilao', lat: 8.2833, lng: 124.9500 },
            { name: 'Impasugong', lat: 8.3000, lng: 125.0167 },
            { name: 'Lantapan', lat: 8.0000, lng: 125.0333 },
            { name: 'Talakag', lat: 8.2333, lng: 124.6000 },
            { name: 'San Fernando, Bukidnon', lat: 7.9167, lng: 125.3333 },
            { name: 'Cabanglasan', lat: 7.9000, lng: 125.3833 },
            { name: 'Malitbog, Bukidnon', lat: 8.5167, lng: 125.0833 }
        ];

        // Auto-load alerts for monitored locations
        async function loadAlertsForMonitoredLocations() {
            if (isLoadingAlerts) return;
            isLoadingAlerts = true;

            console.log('🔍 Checking alerts for monitored locations...');
            
            for (const location of monitoredLocations) {
                try {
                    const bounds = map.getBounds();
                    if (!bounds.contains([location.lat, location.lng])) {
                        continue;
                    }

                    const weatherData = await fetchOpenMeteoData(location.lat, location.lng);
                    await analyzeWeatherForAlerts(weatherData, location.lat, location.lng, location.name);
                    
                    await new Promise(resolve => setTimeout(resolve, 200));
                } catch (error) {
                    console.error(`Error loading alerts for ${location.name}:`, error);
                }
            }

            isLoadingAlerts = false;
            console.log('✅ Alert check complete');
        }

        // Load alerts when map is ready
        map.whenReady(function () {
            document.getElementById('mapLoader').style.display = 'none';
            console.log('🗺️ Map ready - loading alerts automatically...');
            loadAlertsForMonitoredLocations();
        });

        // Reload alerts when map moves or zooms
        let alertRefreshTimeout;
        map.on('moveend', function() {
            clearTimeout(alertRefreshTimeout);
            alertRefreshTimeout = setTimeout(() => {
                loadAlertsForMonitoredLocations();
            }, 1000);
        });

        // Map style switcher
        document.getElementById('mapStyle').addEventListener('change', function () {
            const style = this.value;
            map.removeLayer(currentBaseLayer);
            currentBaseLayer = baseLayers[style];
            currentBaseLayer.addTo(map);
        });

        // Weather layer switch functionality
        const weatherSwitches = document.querySelectorAll('.weather-switch');

        weatherSwitches.forEach(switchElement => {
            switchElement.addEventListener('change', () => {
                const layerType = switchElement.dataset.layer;
                const switchContainer = switchElement.nextElementSibling;

                if (switchElement.checked) {
                    switchContainer.classList.add('switch-loading');
                    setTimeout(() => {
                        switchContainer.classList.remove('switch-loading');
                        activeWeatherLayers.add(layerType);
                        weatherLayers[layerType].addTo(map);
                        console.log(`✅ ${layerType} layer enabled`);
                    }, 800);
                } else {
                    switchContainer.classList.add('switch-loading');
                    setTimeout(() => {
                        switchContainer.classList.remove('switch-loading');
                        activeWeatherLayers.delete(layerType);
                        map.removeLayer(weatherLayers[layerType]);
                        console.log(`❌ ${layerType} layer disabled`);
                    }, 500);
                }
            });
        });

        // Close weather panel function
        let clickMarker = null;
        function closeWeatherPanel() {
            document.getElementById('weatherPanel').classList.add('hidden');
            document.body.classList.remove('panel-open');
            if (clickMarker) {
                map.removeLayer(clickMarker);
                clickMarker = null;
            }
        }

        // Click handler for weather data
        map.on('click', async function (e) {
            const lat = e.latlng.lat.toFixed(4);
            const lng = e.latlng.lng.toFixed(4);

            if (clickMarker) {
                map.removeLayer(clickMarker);
            }

            clickMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: `
                        <div class="flex flex-col items-center">
                            <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white animate-pulse"></div>
                            <div class="w-0 h-0 border-l-4 border-r-4 border-t-6 border-l-transparent border-r-transparent border-t-blue-500"></div>
                        </div>
                    `,
                    className: '',
                    iconSize: [16, 24],
                    iconAnchor: [8, 24]
                })
            }).addTo(map);

            const weatherPanel = document.getElementById('weatherPanel');
            weatherPanel.innerHTML = `
                <div class="flex justify-between items-center p-3 sm:p-4 border-b border-gray-200">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="animate-spin rounded-full h-5 w-5 sm:h-6 sm:w-6 border-b-2 border-blue-600"></div>
                        <span class="text-gray-600 font-medium text-sm sm:text-base">Loading...</span>
                    </div>
                    <button onclick="closeWeatherPanel()" class="text-gray-500 hover:text-gray-700 text-xl font-bold p-1">&times;</button>
                </div>
                <div class="p-3 sm:p-4">
                    <div class="text-center text-gray-500 text-sm">Please wait...</div>
                </div>
            `;
            weatherPanel.classList.remove('hidden');
            document.body.classList.add('panel-open');

            try {
                const weatherData = await fetchOpenMeteoData(lat, lng);
                const locationName = await getLocationName(lat, lng);

                weatherPanel.innerHTML = createEnhancedWeatherPopup(weatherData, lat, lng, locationName);

                await analyzeWeatherForAlerts(weatherData, lat, lng, locationName);

            } catch (error) {
                console.error('Error fetching weather data:', error);
                weatherPanel.innerHTML = `
                    <div class="flex justify-between items-center p-3 sm:p-4 border-b border-gray-200">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2 text-sm sm:text-base">
                            <span class="text-red-500">⚠️</span>
                            <span class="hidden sm:inline">Weather Data Unavailable</span>
                            <span class="sm:hidden">Unavailable</span>
                        </h3>
                        <button onclick="closeWeatherPanel()" class="text-gray-500 hover:text-gray-700 text-xl font-bold p-1">&times;</button>
                    </div>
                    <div class="p-3 sm:p-4">
                        <div class="text-xs text-gray-500 p-3 bg-gray-50 rounded-lg">
                            Unable to fetch weather data. Please try another location.
                        </div>
                    </div>
                `;
            }
        });

        // Fetch Open-Meteo weather data
        async function fetchOpenMeteoData(lat, lng) {
            const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m&hourly=temperature_2m,precipitation_probability,precipitation,weather_code,wind_speed_10m&timezone=auto&forecast_days=2`;
            
            const response = await fetch(url);
            if (!response.ok) throw new Error('Open-Meteo API request failed');
            return await response.json();
        }

        // Fetch Open-Meteo weather warnings/alerts
        async function fetchOpenMeteoAlerts(lat, lng) {
            try {
                const url = `https://api.open-meteo.com/v1/warnings?latitude=${lat}&longitude=${lng}`;
                const response = await fetch(url);
                if (!response.ok) return null;
                return await response.json();
            } catch (error) {
                console.error('Error fetching Open-Meteo alerts:', error);
                return null;
            }
        }

        // Get location name using reverse geocoding
        async function getLocationName(lat, lng) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                return data.address?.city || data.address?.town || data.address?.village || data.address?.county || `${lat}, ${lng}`;
            } catch (error) {
                return `${lat}, ${lng}`;
            }
        }

        // Get weather icon based on WMO code
        function getWeatherIcon(wmoCode) {
            const icons = {
                0: '☀️', 1: '🌤️', 2: '⛅', 3: '☁️',
                45: '🌫️', 48: '🌫️',
                51: '🌦️', 53: '🌦️', 55: '🌦️',
                56: '🌧️', 57: '🌧️',
                61: '🌧️', 63: '🌧️', 65: '🌧️',
                66: '🌨️', 67: '🌨️',
                71: '🌨️', 73: '🌨️', 75: '🌨️', 77: '🌨️',
                80: '🌦️', 81: '🌧️', 82: '⛈️',
                85: '🌨️', 86: '🌨️',
                95: '⛈️', 96: '⛈️', 99: '⛈️'
            };
            return icons[wmoCode] || '🌤️';
        }

        // Get weather description
        function getWeatherDescription(wmoCode) {
            const descriptions = {
                0: 'Clear sky', 1: 'Mainly clear', 2: 'Partly cloudy', 3: 'Overcast',
                45: 'Foggy', 48: 'Depositing rime fog',
                51: 'Light drizzle', 53: 'Moderate drizzle', 55: 'Dense drizzle',
                56: 'Light freezing drizzle', 57: 'Dense freezing drizzle',
                61: 'Slight rain', 63: 'Moderate rain', 65: 'Heavy rain',
                66: 'Light freezing rain', 67: 'Heavy freezing rain',
                71: 'Slight snow', 73: 'Moderate snow', 75: 'Heavy snow', 77: 'Snow grains',
                80: 'Slight rain showers', 81: 'Moderate rain showers', 82: 'Violent rain showers',
                85: 'Slight snow showers', 86: 'Heavy snow showers',
                95: 'Thunderstorm', 96: 'Thunderstorm with hail', 99: 'Thunderstorm with heavy hail'
            };
            return descriptions[wmoCode] || 'Unknown';
        }

        // Create enhanced weather popup - MOBILE RESPONSIVE
        function createEnhancedWeatherPopup(data, lat, lng, locationName) {
            const current = data.current;
            const hourly = data.hourly;
            
            const temp = Math.round(current.temperature_2m);
            const feelsLike = Math.round(current.apparent_temperature);
            const humidity = current.relative_humidity_2m;
            const pressure = current.surface_pressure;
            const windSpeed = current.wind_speed_10m;
            const windDir = current.wind_direction_10m;
            const precipitation = current.precipitation || 0;
            const weatherIcon = getWeatherIcon(current.weather_code);
            const weatherDesc = getWeatherDescription(current.weather_code);

            // Process next 6 time slots (18 hours with 3-hour intervals)
            const currentHourIndex = new Date().getHours();
            const next24Hours = [];
            
            for (let i = 0; i < 6; i++) {
                const hourIndex = currentHourIndex + (i * 3);
                if (hourIndex < hourly.time.length) {
                    next24Hours.push({
                        time: new Date(hourly.time[hourIndex]),
                        temp: Math.round(hourly.temperature_2m[hourIndex]),
                        icon: getWeatherIcon(hourly.weather_code[hourIndex]),
                        rainAmount: hourly.precipitation[hourIndex] || 0,
                        rainChance: hourly.precipitation_probability[hourIndex] || 0
                    });
                }
            }

            return `
                <div class="bg-white rounded-lg overflow-hidden w-full max-w-full dark:text-gray-800">
                    <!-- Header -->
                    <div class="flex justify-between items-start p-3 sm:p-4 bg-gradient-to-r from-gray-700 to-gray-800 text-white">
                        <div class="flex items-start gap-2 flex-1 min-w-0">
                            <span class="text-3xl sm:text-4xl flex-shrink-0">${weatherIcon}</span>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-base sm:text-lg leading-tight truncate">${locationName}</h3>
                                <p class="text-xs sm:text-sm opacity-90 truncate">${weatherDesc}</p>
                            </div>
                        </div>
                        <button onclick="closeWeatherPanel()" 
                                class="text-white/80 hover:text-white text-2xl font-bold flex-shrink-0 ml-2 -mt-1">&times;</button>
                    </div>

                    <!-- Stats Grid -->
                    <div class="p-2 sm:p-3 border-b border-gray-100">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded p-2 sm:p-3 border border-orange-200">
                                <div class="text-xs font-medium text-orange-700">🌡️ TEMP</div>
                                <div class="text-lg sm:text-2xl font-bold text-orange-900">${temp}°C</div>
                                <div class="text-xs text-orange-600 truncate">Feels ${feelsLike}°</div>
                            </div>

                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded p-2 sm:p-3 border border-blue-200">
                                <div class="text-xs font-medium text-blue-700">🌧️ RAIN</div>
                                <div class="text-lg sm:text-2xl font-bold text-blue-900">${next24Hours[0]?.rainChance || 0}%</div>
                                <div class="text-xs text-blue-600 truncate">${(next24Hours[0]?.rainAmount || 0).toFixed(1)} mm</div>
                            </div>

                            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded p-2 sm:p-3 border border-teal-200">
                                <div class="text-xs font-medium text-teal-700">🌪️ WIND</div>
                                <div class="text-lg sm:text-2xl font-bold text-teal-900">${windSpeed.toFixed(1)}</div>
                                <div class="text-xs text-teal-600 truncate">m/s · ${getCardinalDirection(windDir)}</div>
                            </div>

                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded p-2 sm:p-3 border border-purple-200">
                                <div class="text-xs font-medium text-purple-700">💧 HUMID</div>
                                <div class="text-lg sm:text-2xl font-bold text-purple-900">${humidity}%</div>
                                <div class="text-xs text-purple-600 truncate">${pressure.toFixed(0)} hPa</div>
                            </div>
                        </div>
                    </div>

                    <!-- Hourly Forecast -->
                    <div class="p-2 sm:p-3">
                        <h4 class="text-xs font-semibold text-gray-600 mb-2">Next 18 Hours</h4>
                        <div class="space-y-1">
                            ${next24Hours.map(hour => `
                                <div class="flex items-center justify-between p-1.5 sm:p-2 rounded bg-gray-50 hover:bg-gray-100">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <span class="text-lg sm:text-xl flex-shrink-0">${hour.icon}</span>
                                        <div class="text-xs sm:text-sm font-medium truncate">${hour.time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-xs text-gray-500">🌧️${hour.rainChance}%</span>
                                        <span class="font-bold text-sm sm:text-base">${hour.temp}°</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-2 sm:p-3 bg-gray-50 border-t border-gray-200">
                        <div class="text-xs text-gray-500 text-center">
                            Updated: ${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                        </div>
                    </div>
                </div>
            `;
        }

        function getCardinalDirection(deg) {
            const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            const index = Math.round(deg / 45) % 8;
            return directions[index];
        }

        // Analyze weather and create alert at clicked location
        async function analyzeWeatherForAlerts(data, lat, lng, locationName) {
            try {
                const current = data.current;
                const hourly = data.hourly;
                
                const officialAlerts = await fetchOpenMeteoAlerts(lat, lng);
                
                const next6Hours = hourly.precipitation.slice(0, 6);
                const maxPrecip = Math.max(...next6Hours);
                const avgPrecipProb = next6Hours.reduce((a, b) => a + (hourly.precipitation_probability[hourly.precipitation.indexOf(b)] || 0), 0) / 6;

                const weatherData = {
                    temperature: Math.round(current.temperature_2m),
                    rain_amount: maxPrecip,
                    rain_chance: Math.round(avgPrecipProb),
                    wind_speed: current.wind_speed_10m * 3.6,
                    wind_gusts: (current.wind_gusts_10m || current.wind_speed_10m) * 3.6,
                    wind_direction: current.wind_direction_10m,
                    humidity: current.relative_humidity_2m,
                    weather_code: current.weather_code
                };

                const location = {
                    latitude: lat,
                    longitude: lng,
                    name: locationName
                };

                let allAlerts = [];
                
                if (officialAlerts && officialAlerts.warnings && officialAlerts.warnings.length > 0) {
                    officialAlerts.warnings.forEach(warning => {
                        allAlerts.push({
                            type: 'official_warning',
                            severity: mapOpenMeteoSeverity(warning.severity),
                            title: warning.event || 'Weather Warning',
                            description: warning.headline || warning.description || 'Official weather warning issued',
                            conditions: {
                                source: 'Official Alert',
                                onset: warning.onset ? new Date(warning.onset).toLocaleString() : 'Now',
                                expires: warning.expires ? new Date(warning.expires).toLocaleString() : 'Unknown'
                            },
                            isOfficial: true
                        });
                    });
                    console.log(`📢 ${officialAlerts.warnings.length} official warning(s) from Open-Meteo`);
                }

                const analyzedAlerts = analyzeWeatherDataForAlerts(weatherData, location);
                allAlerts = allAlerts.concat(analyzedAlerts);

                if (allAlerts.length > 0) {
                    console.log(`⚠️ Total ${allAlerts.length} alert(s) for ${locationName}`);
                    
                    const locationKey = `${parseFloat(lat).toFixed(4)},${parseFloat(lng).toFixed(4)}`;
                    activeAlertLocations.set(locationKey, {
                        latitude: parseFloat(lat),
                        longitude: parseFloat(lng),
                        location_name: locationName,
                        alerts: allAlerts,
                        timestamp: Date.now()
                    });

                    updateAlertMarkers();
                } else {
                    const locationKey = `${parseFloat(lat).toFixed(4)},${parseFloat(lng).toFixed(4)}`;
                    if (activeAlertLocations.has(locationKey)) {
                        activeAlertLocations.delete(locationKey);
                        updateAlertMarkers();
                    }
                }

            } catch (error) {
                console.error('Error analyzing weather for alerts:', error);
            }
        }

        function mapOpenMeteoSeverity(severity) {
            const severityMap = {
                'extreme': 'extreme',
                'severe': 'high',
                'moderate': 'moderate',
                'minor': 'low',
                'unknown': 'moderate'
            };
            return severityMap[severity?.toLowerCase()] || 'moderate';
        }

        function analyzeWeatherDataForAlerts(weatherData, location) {
            const alerts = [];
            const temp = weatherData.temperature;
            const rain = weatherData.rain_amount;
            const rainChance = weatherData.rain_chance;
            const wind = weatherData.wind_speed;
            const windGusts = weatherData.wind_gusts;
            const humidity = weatherData.humidity;
            const weatherCode = weatherData.weather_code;

            if (temp >= 35) {
                alerts.push({
                    type: temp >= 40 ? 'extreme_heat' : 'heat',
                    severity: temp >= 40 ? 'extreme' : 'high',
                    title: temp >= 40 ? 'Extreme Heat Warning' : 'High Temperature Alert',
                    description: `Temperature is at ${temp}°C, which poses significant health risks.`,
                    conditions: {
                        temperature: `${temp}°C`,
                        threshold: '35°C'
                    }
                });
            } else if (temp <= 10) {
                alerts.push({
                    type: 'cold',
                    severity: temp <= 5 ? 'high' : 'moderate',
                    title: temp <= 5 ? 'Extreme Cold Warning' : 'Cold Weather Alert',
                    description: `Temperature has dropped to ${temp}°C. Cold weather precautions advised.`,
                    conditions: {
                        temperature: `${temp}°C`,
                        threshold: '10°C'
                    }
                });
            }

            if (rain > 10 || rainChance > 70 || [61, 63, 65, 80, 81, 82].includes(weatherCode)) {
                const severity = calculateRainSeverity(rain, rainChance);
                alerts.push({
                    type: 'heavy_rain',
                    severity: severity,
                    title: severity === 'extreme' ? 'Extreme Rainfall Warning' : 'Heavy Rain Alert',
                    description: `Heavy rainfall detected: ${rain.toFixed(1)}mm with ${rainChance}% probability.`,
                    conditions: {
                        rain_amount: `${rain.toFixed(1)} mm`,
                        rain_chance: `${rainChance}%`,
                        humidity: `${humidity}%`
                    }
                });
            }

            if (rain > 15 || (rain > 8 && rainChance > 70)) {
                alerts.push({
                    type: 'flood_risk',
                    severity: rain > 20 ? 'extreme' : 'high',
                    title: 'Flood Risk Warning',
                    description: 'High rainfall intensity may cause flooding in low-lying areas.',
                    conditions: {
                        rain_amount: `${rain.toFixed(1)} mm`,
                        rain_chance: `${rainChance}%`
                    }
                });
            }

            if (wind >= 40 || windGusts >= 50) {
                alerts.push({
                    type: 'strong_wind',
                    severity: (wind >= 60 || windGusts >= 75) ? 'extreme' : 'high',
                    title: (wind >= 60 || windGusts >= 75) ? 'Extreme Wind Warning' : 'Strong Wind Alert',
                    description: `Wind speeds reaching ${wind.toFixed(1)} km/h (gusts: ${windGusts.toFixed(1)} km/h). Potential for property damage.`,
                    conditions: {
                        wind_speed: `${wind.toFixed(1)} km/h`,
                        wind_gusts: `${windGusts.toFixed(1)} km/h`,
                        threshold: '40 km/h'
                    }
                });
            }

            if ([95, 96, 99].includes(weatherCode)) {
                alerts.push({
                    type: 'storm',
                    severity: 'extreme',
                    title: weatherCode >= 96 ? 'Severe Thunderstorm with Hail' : 'Severe Thunderstorm Warning',
                    description: 'Dangerous thunderstorm activity detected. Seek shelter immediately.',
                    conditions: {
                        weather: getWeatherDescription(weatherCode),
                        wind_speed: `${wind.toFixed(1)} km/h`
                    }
                });
            }

            if ((rain > 10 && wind > 30) || (rainChance > 70 && wind > 40)) {
                alerts.push({
                    type: 'storm',
                    severity: 'extreme',
                    title: 'Severe Storm Warning',
                    description: 'Dangerous combination of heavy rain and strong winds detected.',
                    conditions: {
                        rain_amount: `${rain.toFixed(1)} mm`,
                        wind_speed: `${wind.toFixed(1)} km/h`,
                        combined_threat: 'Yes'
                    }
                });
            }

            return alerts;
        }

        function calculateRainSeverity(amount, chance) {
            if (amount > 30 || (amount > 20 && chance > 90)) return 'extreme';
            if (amount > 20 || (amount > 15 && chance > 80)) return 'high';
            if (amount > 10 || chance > 70) return 'moderate';
            return 'low';
        }

        function updateAlertMarkers() {
            alertMarkersLayer.clearLayers();

            activeAlertLocations.forEach((locationData, key) => {
                const relevantAlerts = locationData.alerts;

                if (relevantAlerts.length > 0) {
                    const highestSeverity = getHighestSeverityFromAlerts(relevantAlerts);
                    const marker = createAlertMarkerForLocation(locationData, relevantAlerts, highestSeverity);
                    alertMarkersLayer.addLayer(marker);
                }
            });
        }

        function getHighestSeverityFromAlerts(alerts) {
            const severityOrder = { extreme: 5, high: 4, moderate: 3, low: 2, info: 1 };
            let highest = 'info';
            let highestValue = 0;

            alerts.forEach(alert => {
                const value = severityOrder[alert.severity] || 0;
                if (value > highestValue) {
                    highestValue = value;
                    highest = alert.severity;
                }
            });

            return highest;
        }

        function getAlertIconConfig(severity) {
            const configs = {
                extreme: {
                    icon: '🚨',
                    bgClass: 'bg-red-600',
                    borderClass: 'border-red-800',
                    pulseClass: 'bg-red-600'
                },
                high: {
                    icon: '⚠️',
                    bgClass: 'bg-orange-500',
                    borderClass: 'border-orange-700',
                    pulseClass: 'bg-orange-500'
                },
                moderate: {
                    icon: '⚡',
                    bgClass: 'bg-amber-500',
                    borderClass: 'border-amber-700',
                    pulseClass: 'bg-amber-500'
                },
                low: {
                    icon: '🔔',
                    bgClass: 'bg-yellow-400',
                    borderClass: 'border-yellow-600',
                    pulseClass: 'bg-yellow-400'
                },
                info: {
                    icon: 'ℹ️',
                    bgClass: 'bg-blue-500',
                    borderClass: 'border-blue-700',
                    pulseClass: 'bg-blue-500'
                }
            };
            return configs[severity] || configs.info;
        }

        function getAlertTypeIcon(type) {
            const icons = {
                heat: '🌡️',
                extreme_heat: '🌡️',
                cold: '❄️',
                heavy_rain: '🌧️',
                flood_risk: '🌊',
                strong_wind: '💨',
                storm: '⛈️',
                official_warning: '📢'
            };
            return icons[type] || '⚠️';
        }

        function createAlertMarkerForLocation(locationData, alerts, highestSeverity) {
            const config = getAlertIconConfig(highestSeverity);
            const alertCount = alerts.length;
            
            const icon = L.divIcon({
                html: `
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full ${config.pulseClass} animate-ping opacity-75"></div>
                        <div class="${config.bgClass} rounded-full border-4 ${config.borderClass} shadow-xl flex items-center justify-center relative"
                             style="width: 48px; height: 48px;">
                            <span class="text-2xl">${config.icon}</span>
                            ${alertCount > 1 ? `
                                <div class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center border-2 border-white">
                                    ${alertCount}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `,
                className: 'alert-marker',
                iconSize: [48, 48],
                iconAnchor: [24, 24],
                popupAnchor: [0, -24]
            });

            const marker = L.marker([locationData.latitude, locationData.longitude], {
                icon: icon,
                zIndexOffset: 1000
            });

            const tooltipContent = createAlertTooltip(locationData, alerts, highestSeverity);
            marker.bindTooltip(tooltipContent, {
                direction: 'top',
                offset: [0, -20],
                opacity: 0.95,
                className: 'alert-tooltip'
            });

            marker.bindPopup(() => createAlertPopupContent(locationData, alerts, highestSeverity), {
                maxWidth: 350,
                className: 'alert-popup'
            });

            return marker;
        }

        function createAlertTooltip(locationData, alerts, highestSeverity) {
            const config = getAlertIconConfig(highestSeverity);
            const primary = alerts[0];
            const hasOfficialWarnings = alerts.some(a => a.isOfficial);
            
            return `
                <div class="alert-tooltip-content" style="min-width: 220px;">
                    <div class="${config.bgClass} text-white px-3 py-2 -m-2 mb-2 rounded-t">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">${getAlertTypeIcon(primary.type)}</span>
                            <div class="flex-1">
                                <div class="font-bold text-sm">${locationData.location_name}</div>
                                ${hasOfficialWarnings ? '<div class="text-xs opacity-90">⚠️ OFFICIAL WARNING</div>' : ''}
                            </div>
                        </div>
                    </div>
                    <div class="px-2 py-1">
                        <div class="text-xs font-semibold text-gray-700 mb-1">${primary.title}</div>
                        <div class="text-xs text-gray-600 mb-2">${primary.description.substring(0, 80)}${primary.description.length > 80 ? '...' : ''}</div>
                        ${alerts.length > 1 ? `
                            <div class="text-xs text-gray-500 border-t pt-1">
                                +${alerts.length - 1} more alert${alerts.length > 2 ? 's' : ''} - Click for details
                            </div>
                        ` : `
                            <div class="text-xs text-gray-500 border-t pt-1">
                                Click for full details
                            </div>
                        `}
                    </div>
                </div>
            `;
        }

       function createAlertPopupContent(locationData, alerts, highestSeverity) {
            const config = getAlertIconConfig(highestSeverity);
            const primary = alerts[0];
            const hasOfficialWarnings = alerts.some(a => a.isOfficial);

            return `
                <div class="alert-popup-content p-2 sm:p-5 space-y-2 sm:space-y-4" style="width: calc(100vw - 4rem); max-width: 350px;">
                    <div class="${config.bgClass} text-white p-3 sm:p-6 -m-2 sm:-m-5 mb-2 sm:mb-4 rounded-t-lg shadow-inner">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-xl sm:text-3xl flex-shrink-0">${getAlertTypeIcon(primary.type)}</span>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm sm:text-xl leading-tight">${primary.title}</h3>
                                <p class="text-xs sm:text-sm opacity-90 truncate">${locationData.location_name}</p>
                            </div>
                        </div>
                        ${hasOfficialWarnings ? `
                            <div class="mt-2 bg-white/25 rounded-md px-2 py-1 text-xs font-semibold tracking-wide break-words">
                                ⚠️ OFFICIAL WARNING
                            </div>
                        ` : ''}
                    </div>

                    <div class="mb-2 flex flex-wrap items-center gap-1">
                        <span class="inline-block px-2 sm:px-4 py-1 rounded-full text-xs font-bold uppercase ${config.bgClass} text-white whitespace-nowrap">
                            ${highestSeverity} Alert
                        </span>
                        ${alerts.length > 1 ? `
                            <span class="text-xs text-gray-600">
                                +${alerts.length - 1} more
                            </span>
                        ` : ''}
                    </div>

                    <p class="text-gray-700 text-xs sm:text-sm leading-relaxed mb-2">
                        ${primary.description}
                    </p>

                    ${primary.conditions && Object.keys(primary.conditions).length > 0 ? `
                        <div class="border-t pt-2 sm:pt-4 mb-2">
                            <p class="text-xs font-semibold text-gray-600 mb-2">
                                ${primary.isOfficial ? 'Alert Information:' : 'Conditions:'}
                            </p>
                            <div class="grid grid-cols-2 gap-1.5 sm:gap-3">
                                ${Object.entries(primary.conditions).map(([key, value]) => `
                                    <div class="bg-gray-50 rounded-md p-1.5 sm:p-3">
                                        <div class="text-xs text-gray-500 capitalize truncate">${key.replace(/_/g, ' ')}</div>
                                        <div class="text-xs font-semibold text-gray-900 break-words">${value}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}

                    ${alerts.length > 1 ? `
                        <div class="border-t pt-2 sm:pt-4 mb-2">
                            <p class="text-xs font-semibold text-gray-600 mb-2">Active Alerts:</p>
                            <div class="space-y-1">
                                ${alerts.map(alert => `
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <span class="flex-shrink-0 text-sm">${getAlertTypeIcon(alert.type)}</span>
                                        <span class="text-gray-700 flex-1 min-w-0 truncate">${alert.title}</span>
                                        ${alert.isOfficial ? '<span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded flex-shrink-0">Official</span>' : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}

                    <div class="pt-2 text-xs text-gray-500 text-center border-t">
                        ${hasOfficialWarnings ? 'Official alerts • ' : ''}Updated ${new Date(locationData.timestamp).toLocaleTimeString()}
                    </div>
                </div>
            `;
        }

        function getWeatherAtLocation(lat, lng) {
            map.fire('click', {
                latlng: L.latLng(lat, lng)
            });
            map.closePopup();
        }

        // Clear old alerts and refresh periodically
        setInterval(() => {
            const now = Date.now();
            const maxAge = 30 * 60 * 1000;
            
            activeAlertLocations.forEach((data, key) => {
                if (now - data.timestamp > maxAge) {
                    activeAlertLocations.delete(key);
                    console.log(`🗑️ Removed expired alert for ${data.location_name}`);
                }
            });
            
            updateAlertMarkers();
            loadAlertsForMonitoredLocations();
        }, 15 * 60 * 1000);

        console.log('🗺️ Real-time alert system initialized with Open-Meteo API + Official Warnings');
    </script>
@endpush