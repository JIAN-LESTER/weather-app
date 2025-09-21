@extends('layouts.app')

@section('title', 'Maps')
@section('header', 'Maps')

@section('content')


    <div class="relative bg-white rounded-lg shadow-lg border overflow-hidden">
        <!-- Map Loading Indicator -->
        <div id="mapLoader" class="absolute inset-0 bg-gray-50 flex items-center justify-center z-20">
            <div class="flex items-center gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-600 font-medium">Loading map...</span>
            </div>
        </div>

        <!-- Map -->
        <div id="map" class="h-[80vh] w-full z-0"></div>

        <div id="weatherPanel"
            class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border p-4 max-w-sm z-20 hidden">
            <!-- Content will be injected dynamically -->
        </div>


        <!-- Collapsible Header Controls -->
        <details class="absolute bottom-4 right-4 z-10 max-w-xs">
            <summary
                class="cursor-pointer bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border px-4 py-2 text-gray-700 font-medium hover:bg-white">
                ⚙️ Map Controls
            </summary>

            <div
                class="bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border p-4 flex flex-col gap-4 max-h-[60vh] overflow-y-auto mt-2">
                <!-- Map Scope -->
                <div class="flex flex-col gap-2">
                    <label for="mapScope" class="text-sm font-medium text-gray-700">Map Scope:</label>
                    <select id="mapScope"
                        class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="bukidnon" selected>Bukidnon</option>
                        <option value="all">Philippines</option>
                    </select>
                </div>

                <!-- Map Style -->
                <div class="flex flex-col gap-2">
                    <label for="mapStyle" class="text-sm font-medium text-gray-700">Map Style:</label>
                    <select id="mapStyle"
                        class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="light" selected>Light Mode</option>
                        <option value="dark">Dark Mode</option>
                        <option value="satellite">Satellite</option>
                    </select>
                </div>

                <!-- Weather Layers -->
                <div class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-gray-700">Weather Layers:</span>
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="weather-radio flex items-center gap-2 px-4 py-2 rounded-full border border-orange-200 text-orange-700 bg-orange-50 hover:bg-orange-100 transition-all duration-200 active:bg-orange-200"
                            data-layer="temp">
                            🌡️ Temperature
                        </button>

                        <button
                            class="weather-radio flex items-center gap-2 px-4 py-2 rounded-full border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 transition-all duration-200 active:bg-blue-200"
                            data-layer="precipitation">
                            🌧️ Rain
                        </button>
                    </div>
                </div>
            </div>
        </details>





        <!-- Map Legend -->
        <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border p-4 max-w-xs z-10">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">🗺️ Map Legend</h3>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-3 bg-gradient-to-r from-blue-500 to-red-500 rounded opacity-50"></div>
                    <span>Temperature (°C)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-3 bg-purple-400 rounded opacity-60"></div>
                    <span>Rain</span>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        .weather-btn {
            @apply px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200 cursor-pointer select-none;
        }

        .weather-btn.active {
            @apply shadow-md transform scale-105;
        }

        .weather-btn:hover {
            @apply shadow-sm transform translate-y-[-1px];
        }

        /* Custom map controls styling */
        .leaflet-control-layers {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-radius: 8px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(229, 231, 235, 1) !important;
        }

        .leaflet-control-zoom {
            border-radius: 8px !important;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }

        .leaflet-control-zoom a {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            color: #374151 !important;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .leaflet-control-zoom a:hover {
            background: #f3f4f6 !important;
            transform: scale(1.05);
        }

        /* Map popup styling */
        .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }

        .leaflet-popup-tip {
            background: rgba(255, 255, 255, 0.95);
        }

        .weather-popup .leaflet-popup-content {
            margin: 0 !important;
        }

        .custom-loading-marker,
        .custom-weather-marker,
        .custom-error-marker {
            background: transparent !important;
            border: none !important;
        }

        .custom-loading-marker div,
        .custom-weather-marker div,
        .custom-error-marker div {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        /* Loading animation */
        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 0.4;
            }

            50% {
                opacity: 1;
            }
        }

        .animate-pulse-dot {
            animation: pulse-dot 2s infinite;
        }
    </style>

    <script>
        const openWeatherKey = "{{ env('OPENWEATHER_API_KEY') }}";

        // Enhanced boundaries with buffer for better UX
        const bukidnonBounds = L.latLngBounds([7.2, 124.2], [8.6, 125.9]);
        const philippinesBounds = L.latLngBounds([4.0, 115.5], [21.5, 128.0]);

        // Initialize map with loading state
        const map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([7.9, 125.1], 10);

        // Add custom zoom control
        L.control.zoom({
            position: 'topright'
        }).addTo(map);

        // Enhanced base layers
        const baseLayers = {
            light: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 18
            }),
            dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OSM &copy; CARTO',
                maxZoom: 18
            }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '&copy; Esri &copy; DigitalGlobe',
                maxZoom: 18
            })
        };

        // Start with light theme
        let currentBaseLayer = baseLayers.light;
        currentBaseLayer.addTo(map);

        // Weather overlay layers with enhanced styling
        const weatherLayers = {
            clouds: L.tileLayer(`https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 0.4,
                attribution: 'Weather data © OpenWeatherMap'
            }),
            temp: L.tileLayer(`https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 1,
                attribution: 'Weather data © OpenWeatherMap'
            }),
            precipitation: L.tileLayer(`https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 1,
                attribution: 'Weather data © OpenWeatherMap'
            }),
            wind: L.tileLayer(`https://tile.openweathermap.org/map/wind_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 1,
                attribution: 'Weather data © OpenWeatherMap'
            })
        };

        // Storm layer group
        const precipitationLayer = L.layerGroup([weatherLayers.precipitation, weatherLayers.wind]);

        // Track active weather layers
        let activeWeatherLayers = new Set();

        // Set initial bounds
        map.setMaxBounds(bukidnonBounds);
        map.on('drag', function () {
            map.panInsideBounds(map.getBounds(), { animate: false });
        });

        // Hide loader when map is ready
        map.whenReady(function () {
            setTimeout(() => {
                document.getElementById('mapLoader').style.display = 'none';
                updateLastUpdated();
            }, 1000);
        });

        document.getElementById('mapScope').addEventListener('change', function () {
            const scope = this.value;
            const scopeDisplay = document.getElementById('currentScope');

            if (scope === 'bukidnon') {
                // Temporarily remove bounds to allow full zoom/fly
                map.setMaxBounds(null);
                map.flyTo([7.9, 125.1], 10, { duration: 1.5 });

                // Restore bounds after animation
                setTimeout(() => {
                    map.setMaxBounds(bukidnonBounds);
                }, 1600); // slightly longer than duration

                scopeDisplay.textContent = 'Bukidnon Province';
            } else {
                map.setMaxBounds(null);
                map.flyTo([12.5, 122.5], 5, { duration: 1.5 });
                setTimeout(() => {
                    map.setMaxBounds(philippinesBounds);
                }, 1600);

                scopeDisplay.textContent = 'All Philippines';
            }

            updateLastUpdated();
        });




        // Map style switcher
        document.getElementById('mapStyle').addEventListener('change', function () {
            const style = this.value;

            // Remove current base layer
            map.removeLayer(currentBaseLayer);

            // Add new base layer
            currentBaseLayer = baseLayers[style];
            currentBaseLayer.addTo(map);

            updateLastUpdated();
        });

        // Weather layer toggle functionality
        document.querySelectorAll('.weather-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const layerType = this.dataset.layer;

                if (this.classList.contains('active')) {
                    // Remove layer
                    this.classList.remove('active');
                    activeWeatherLayers.delete(layerType);

                    if (layerType === 'precipitation') {
                        map.removeLayer(precipitationLayer);
                    } else {
                        map.removeLayer(weatherLayers[layerType]);
                    }
                } else {
                    // Add layer
                    this.classList.add('active');
                    activeWeatherLayers.add(layerType);

                    if (layerType === 'precipitation') {
                        precipitationLayer.addTo(map);
                    } else {
                        weatherLayers[layerType].addTo(map);
                    }
                }

                updateLastUpdated();
            });
        });

        // Update timestamp
        function updateLastUpdated() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('lastUpdate').textContent = timeString;
        }

        // Add sample markers with enhanced popups
        function addSampleMarkers() {
            const bukidnonCities = [

            ];

            bukidnonCities.forEach(city => {
                const marker = L.marker(city.coords).addTo(map);
                marker.bindPopup(`
                            <div class="p-2">
                                <h3 class="font-bold text-gray-800 mb-2">${city.name}</h3>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Temperature:</span>
                                        <span class="font-medium text-orange-600">${city.temp}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Conditions:</span>
                                        <span class="font-medium text-blue-600">${city.weather}</span>
                                    </div>
                                </div>
                            </div>
                        `);
            });
        }

        // Load sample markers
        addSampleMarkers();

        // Optional: Load markers dynamically from Laravel backend
        function loadMarkers(scope) {
            // Enhanced API call with loading states
            // fetch(`/api/locations?scope=${scope}`)
            //     .then(response => response.json())
            //     .then(data => {
            //         // Process and display markers
            //         updateLastUpdated();
            //     })
            //     .catch(error => console.error('Error loading markers:', error));
        }

        // Click handler for weather data
        let clickMarker = null;

        map.on('click', async function (e) {
            const lat = e.latlng.lat.toFixed(4);
            const lng = e.latlng.lng.toFixed(4);

            // Remove previous marker
            if (clickMarker) {
                map.removeLayer(clickMarker);
            }

            // Add marker
            clickMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: `
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white"></div>
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
                        <div class="flex justify-center items-center p-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <span class="ml-2 text-gray-600 font-medium">Loading weather...</span>
                        </div>
                    `;
            weatherPanel.classList.remove('hidden');

            try {
                // Fetch current weather and full day forecast concurrently
                const [currentWeather, fullDayForecast] = await Promise.all([
                    fetchCurrentWeatherData(lat, lng),
                    fetchFullDayForecastData(lat, lng)
                ]);

                // Store globally
                window.currentWeatherData = currentWeather;
                window.fullDayForecastData = fullDayForecast;

                // Render panel
                weatherPanel.innerHTML = createFullDayWeatherPopup(currentWeather, fullDayForecast, lat, lng);

            } catch (error) {
                console.error('Error fetching weather data:', error);
                weatherPanel.innerHTML = `
                            <div class="p-3">
                                <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <span class="text-red-500">⚠️</span>
                                    Weather Data Unavailable
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Latitude:</span>
                                        <span class="font-mono font-medium">${lat}°</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Longitude:</span>
                                        <span class="font-mono font-medium">${lng}°</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-2 p-2 bg-gray-50 rounded">
                                        Unable to fetch weather data.
                                    </div>
                                </div>
                            </div>
                        `;
            }
        });


        // Fetch current weather (single point)
        async function fetchCurrentWeatherData(lat, lng) {
            try {
                const response = await fetch(`/weather/current-weather?lat=${lat}&lon=${lng}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                if (data.error) throw new Error('Failed to fetch current weather data');
                return data;
            } catch (error) {
                console.error('Error fetching current weather data:', error);
                throw error;
            }
        }

        // Fetch full day forecast for all time slots
        async function fetchFullDayForecastData(lat, lng) {
            try {
                const response = await fetch(`/weather/full-day-forecast?lat=${lat}&lon=${lng}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                if (data.error) throw new Error('Failed to fetch full day forecast');
                return data;
            } catch (error) {
                console.error('Error fetching full day forecast data:', error);
                throw error;
            }
        }

        // Optional: reverse geocoding to get location name
        async function fetchLocationName(lat, lng) {
            try {
                const response = await fetch(
                    `https://api.openweathermap.org/geo/1.0/reverse?lat=${lat}&lon=${lng}&limit=1&appid=${openWeatherKey}`
                );
                if (!response.ok) return null;

                const data = await response.json();
                if (data && data.length > 0) {
                    const location = data[0];
                    return `${location.name}${location.state ? ', ' + location.state : ''}, ${location.country}`;
                }
            } catch (error) {
                console.warn('Reverse geocoding failed:', error);
            }
            return null;
        }


  
function createFullDayWeatherPopup(currentWeather, fullDayForecast, lat, lng) {
    const temp = Math.round(currentWeather.main.temp);
    const feelsLike = Math.round(currentWeather.main.feels_like);

    // Fixed rain amount handling
    const rainAmount = currentWeather.precipitation_mm ?? 
                      (currentWeather.rain?.['1h'] ?? 0);

    // Enhanced rain chance calculation - consistent with dashboard logic
    let rainChance;
    if (currentWeather.precipitation_chance !== undefined) {
        rainChance = Math.round(currentWeather.precipitation_chance);
    } else if (currentWeather.pop !== undefined) {
        rainChance = Math.round(currentWeather.pop * 100);
    } else if (rainAmount > 0) {
        rainChance = 100; // If there's actual rain, chance is 100%
    } else {
        // Enhanced fallback logic matching dashboard
        const weatherMain = currentWeather.weather?.[0]?.main?.toLowerCase() || '';
        const weatherDesc = currentWeather.weather?.[0]?.description?.toLowerCase() || '';
        const cloudiness = currentWeather.clouds?.all ?? 0;
        const humidity = currentWeather.main?.humidity ?? 0;
        
        if (weatherMain.includes('rain') || weatherMain.includes('thunderstorm')) {
            rainChance = Math.max(80, Math.min(100, humidity + cloudiness / 2));
        } else if (weatherMain.includes('drizzle')) {
            rainChance = Math.max(60, Math.min(90, humidity));
        } else if (weatherMain.includes('cloud')) {
            if (humidity > 85) rainChance = Math.round(humidity * 0.8);
            else if (humidity > 70) rainChance = Math.round(humidity * 0.6);
            else rainChance = Math.round(humidity * 0.4);
        } else {
            // Clear conditions
            if (humidity > 85) rainChance = Math.round(humidity * 0.4);
            else if (humidity > 70) rainChance = Math.round(humidity * 0.2);
            else rainChance = Math.round(humidity * 0.1);
        }
    }
    
    // Ensure rain chance is within bounds
    rainChance = Math.max(0, Math.min(100, rainChance));

    const locationName = currentWeather.name || fullDayForecast.location.name || 'Unknown Location';
    const country = currentWeather.sys?.country || fullDayForecast.location.country || '';
    const rawDesc = currentWeather.weather?.[0]?.description || 'No data';
    const weatherDesc = toTitleCase(rawDesc);
    const weatherIcon = currentWeather.weather?.[0]?.icon || '01d';

    // Enhanced storm status with better thresholds
    let currentStormStatus = 'Clear';
    let currentStormColor = 'text-green-600';
    let currentStormBg = 'bg-green-50';
    
    if (rainChance > 80) {
        currentStormStatus = 'Heavy Rain';
        currentStormColor = 'text-red-600';
        currentStormBg = 'bg-red-50';
    } else if (rainChance > 60) {
        currentStormStatus = 'Moderate Rain';
        currentStormColor = 'text-orange-600';
        currentStormBg = 'bg-orange-50';
    } else if (rainChance > 30) {
        currentStormStatus = 'Light Rain';
        currentStormColor = 'text-yellow-600';
        currentStormBg = 'bg-yellow-50';
    } else if (rainChance > 10) {
        currentStormStatus = 'Possible Rain';
        currentStormColor = 'text-blue-600';
        currentStormBg = 'bg-blue-50';
    }

    // Fixed forecast cards with better data handling
    const timeSlots = fullDayForecast.time_slots;
    const periods = [
        { key: 'morning', label: 'MORNING', short: 'MORN' },
        { key: 'noon', label: 'NOON', short: 'NOON' },
        { key: 'afternoon', label: 'AFTERNOON', short: 'AFT' },
        { key: 'evening', label: 'EVENING', short: 'EVE' }
    ];
    
    const forecastCards = periods.map(period => {
        const data = timeSlots[period.key];
        
        if (!data || data.temperature === undefined) {
            return `
                <div class="bg-gray-100 p-2 rounded text-center min-h-[80px] flex flex-col justify-center">
                    <div class="text-xs text-gray-500 font-medium mb-1">${period.short}</div>
                    <div class="text-sm text-gray-400">No data</div>
                </div>
            `;
        }

        const periodTemp = Math.round(data.temperature);
        // Enhanced rain amount calculation for periods
        let periodRainAmount = data.precipitation_mm ?? (data.rain?.['1h'] ?? 0);
        
        // Enhanced estimation for periods if no direct rain data
        if (periodRainAmount === 0) {
            const periodWeatherMain = (data.weather_main || '').toLowerCase();
            const periodWeatherDesc = (data.weather_desc || '').toLowerCase();
            const periodCloudiness = data.cloudiness ?? 0;
            const periodHumidity = data.humidity ?? 50;
            
            if (periodWeatherMain.includes('rain') || periodWeatherDesc.includes('rain')) {
                if (periodWeatherDesc.includes('heavy')) periodRainAmount = 5.0;
                else if (periodWeatherDesc.includes('moderate')) periodRainAmount = 2.0;
                else if (periodWeatherDesc.includes('light')) periodRainAmount = 0.5;
                else periodRainAmount = 1.0; // general rain
            } else if (periodWeatherMain.includes('drizzle')) {
                periodRainAmount = 0.2;
            } else if (periodWeatherMain.includes('thunderstorm')) {
                periodRainAmount = 8.0;
            } else if (periodHumidity > 90 && periodCloudiness > 80) {
                periodRainAmount = 0.1; // Very likely to rain soon
            }
        }

        // Enhanced rain chance calculation for periods
        let periodRainChance;
        if (data.precipitation_chance !== undefined) {
            periodRainChance = Math.round(data.precipitation_chance);
        } else if (data.pop !== undefined) {
            periodRainChance = Math.round(data.pop * 100);
        } else if (periodRainAmount > 0) {
            periodRainChance = 100;
        } else {
            // Enhanced calculation matching dashboard logic
            const periodWeatherMain = (data.weather_main || '').toLowerCase();
            const periodWeatherDesc = (data.weather_desc || '').toLowerCase();
            const periodCloudiness = data.cloudiness ?? 0;
            const periodHumidity = data.humidity ?? 50;
            
            if (periodWeatherMain.includes('rain') || periodWeatherMain.includes('thunderstorm')) {
                periodRainChance = Math.max(80, Math.min(100, periodHumidity + periodCloudiness / 2));
            } else if (periodWeatherMain.includes('drizzle')) {
                periodRainChance = Math.max(60, Math.min(90, periodHumidity));
            } else if (periodWeatherMain.includes('cloud')) {
                if (periodHumidity > 85) periodRainChance = Math.round(periodHumidity * 0.8);
                else if (periodHumidity > 70) periodRainChance = Math.round(periodHumidity * 0.6);
                else periodRainChance = Math.round(periodHumidity * 0.4);
            } else {
                // Clear conditions
                if (periodHumidity > 85) periodRainChance = Math.round(periodHumidity * 0.4);
                else if (periodHumidity > 70) periodRainChance = Math.round(periodHumidity * 0.2);
                else periodRainChance = Math.round(periodHumidity * 0.1);
            }
            
            // Ensure within bounds
            periodRainChance = Math.max(0, Math.min(100, periodRainChance));
        }

        // Color coding for period rain chance
        let periodStormColor = 'text-green-600';
        if (periodRainChance > 80) periodStormColor = 'text-red-600';
        else if (periodRainChance > 60) periodStormColor = 'text-orange-600';
        else if (periodRainChance > 30) periodStormColor = 'text-yellow-600';
        else if (periodRainChance > 10) periodStormColor = 'text-blue-600';

        return `
            <div class="bg-blue-50 p-2 rounded text-center min-h-[80px] border border-blue-200 flex flex-col justify-center">
                <div class="text-xs text-blue-600 font-medium mb-1">${period.short}</div>
                <div class="text-base font-bold text-blue-700 mb-1">${periodTemp}°</div>
                <div class="text-xs ${periodStormColor} font-medium">${periodRainChance}%</div>
                <div class="text-xs ${periodStormColor}">${periodRainAmount.toFixed(1)}mm</div>
            </div>
        `;
    }).join('');

    // Format coordinates properly
    const formattedLat = parseFloat(lat).toFixed(4);
    const formattedLng = parseFloat(lng).toFixed(4);

    return `
        <div class="p-4 max-w-lg w-full overflow-hidden rounded-lg">
            <!-- Header Section -->
            <div class="flex items-start justify-between mb-4 gap-3">
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-gray-800 text-lg truncate" title="${locationName}">
                        ${locationName}
                    </h3>
                    ${country ? `<p class="text-xs text-gray-500 truncate">${country}</p>` : ''}
                    <p class="text-xs text-green-600 font-medium">Current Weather</p>
                </div>
                <div class="flex-shrink-0">
                    <img src="https://openweathermap.org/img/wn/${weatherIcon}@2x.png" 
                         alt="${rawDesc}" class="w-12 h-12" />
                </div>
            </div>

            <!-- Current Weather Grid -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-orange-50 p-3 rounded-lg border border-orange-200">
                    <div class="text-xs text-orange-600 font-medium mb-1">TEMPERATURE</div>
                    <div class="text-xl font-bold text-orange-700">${temp}°C</div>
                    <div class="text-sm text-orange-500">Feels ${feelsLike}°C</div>
                </div>
                 <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <div class="text-xs  font-medium mb-1 text-blue-600">PRECIPITATION</div>
                    <div class="text-xl font-bold text-blue-700">${rainChance}%</div>
                    <div class="text-sm text-blue-500">
                        ${rainAmount > 0 ? rainAmount.toFixed(1) + ' mm/h' : '0 mm/h'}
                    </div>
                </div>
            </div>


            <!-- Today's Forecast -->
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    🌦️ Today's Forecast
                </h4>
                <div class="grid grid-cols-4 gap-2">
                    ${forecastCards}
                </div>
            </div>

         

            <!-- Footer -->
            <div class="pt-3 border-t border-gray-200 text-xs text-gray-500">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">📍 Location:</span>
                    <span class="font-mono">${formattedLat}°, ${formattedLng}°</span>
                </div>
                <div class="text-center text-blue-600 font-medium">
                    Weather data updates every 10 minutes
                </div>
            </div>
        </div>
    `;
}

// Helper function for title case conversion
function toTitleCase(str) {
    if (!str) return '';
    return str.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}


        // Function to save full day forecast
        async function saveFullDayForecast(lat, lng) {
            const saveBtn = document.getElementById('saveFullDayBtn');
            const originalContent = saveBtn.innerHTML;

            // Show loading state
            saveBtn.disabled = true;
            saveBtn.innerHTML = `
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                        Saving Full Day...
                    `;

            try {
                const fullDayData = window.fullDayForecastData;

                if (!fullDayData || !fullDayData.time_slots) {
                    throw new Error('Full day forecast data not available');
                }

                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfToken) {
                    throw new Error('CSRF token not found. Please refresh the page.');
                }

                // Prepare payload with proper structure for JSON storage
                const postData = {
                    latitude: parseFloat(lat),
                    longitude: parseFloat(lng),
                    location_name: fullDayData.location.name || `Location (${lat}, ${lng})`,
                    time_slots: {},
                    _token: csrfToken
                };

                // Process each time slot from the forecast data
                const timePeriods = ['morning', 'noon', 'afternoon', 'evening'];

                for (const period of timePeriods) {
                    const periodData = fullDayData.time_slots[period];

                    if (periodData && periodData.temperature !== undefined) {
                        postData.time_slots[period] = {
                            temperature: parseFloat(periodData.temperature),
                            feels_like: parseFloat(periodData.feels_like || periodData.temperature),
                            precipitation_mm: parseFloat(periodData.precipitation_mm || 0),
                            precipitation_chance: parseInt(periodData.precipitation_chance || 0),
                            weather_main: periodData.weather_main || '',
                            weather_desc: periodData.weather_desc || '',
                            weather_icon: periodData.weather_icon || '',
                            humidity: parseInt(periodData.humidity || 0),
                            pressure: parseInt(periodData.pressure || 0),
                            wind_speed: parseFloat(periodData.wind_speed || 0),
                            wind_direction: periodData.wind_direction || '0',
                            cloudiness: parseInt(periodData.cloudiness || 0),
                            forecast_time: periodData.forecast_time || null
                        };

                    }
                }

                // Check if we have at least one valid time slot
                if (Object.keys(postData.time_slots).length === 0) {
                    throw new Error('No valid weather data available for any time period');
                }

                console.log('Sending JSON payload:', postData); // Debug logging

                const response = await fetch('/weather/store-full-day-snapshots', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(postData)
                });

                let result;
                try {
                    result = await response.json();
                } catch (parseError) {
                    console.error('Failed to parse response:', parseError);
                    throw new Error('Server returned invalid response');
                }

                if (!response.ok) {
                    throw new Error(result.message || `HTTP ${response.status}: Failed to save forecast`);
                }

                if (result.success) {
                    saveBtn.innerHTML = `
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Full Day Saved! (${result.data.total_periods} periods)
                            `;
                    saveBtn.className = 'w-full bg-emerald-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center gap-2';

                    showNotification(
                        `Full day weather data saved for ${result.data.total_periods} time periods: ${result.data.time_periods_saved.join(', ')}`,
                        'success'
                    );

                    // Refresh the snapshots display if the function exists
                    if (typeof loadTodaysSnapshots === 'function') {
                        setTimeout(loadTodaysSnapshots, 1000);
                    }
                } else {
                    throw new Error(result.message || 'Unknown error occurred');
                }

            } catch (error) {
                console.error('Error saving full day forecast:', error);

                saveBtn.innerHTML = `
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Save Failed
                        `;
                saveBtn.className = 'w-full bg-red-600 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center gap-2';

                showNotification(error.message || 'Failed to save full day forecast.', 'error');
            } finally {
                // Reset button after delay
                setTimeout(() => {
                    saveBtn.innerHTML = originalContent;
                    saveBtn.className = 'w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2';
                    saveBtn.disabled = false;
                }, 3000);
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.weather-notification');
            existingNotifications.forEach(notif => notif.remove());

            const notification = document.createElement('div');
            notification.className = `weather-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;

            // Set colors based on type
            if (type === 'success') {
                notification.className += ' bg-green-100 border border-green-400 text-green-700';
            } else if (type === 'error') {
                notification.className += ' bg-red-100 border border-red-400 text-red-700';
            } else {
                notification.className += ' bg-blue-100 border border-blue-400 text-blue-700';
            }

            notification.innerHTML = `
                        <div class="flex items-center">
                            <div class="flex-1">${message}</div>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-lg">&times;</button>
                        </div>
                    `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        // Function to load today's weather snapshots (for dashboard)
        async function loadTodaysSnapshots() {
            try {
                const response = await fetch('/weather/todays-snapshots', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    displaySnapshotsOnMap(result.snapshots);
                } else {
                    console.error('Failed to load snapshots:', result.message);
                }

            } catch (error) {
                console.error('Error loading snapshots:', error);
            }
        }


        // Load existing snapshots when map is ready
        map.whenReady(function () {
            setTimeout(() => {
                document.getElementById('mapLoader').style.display = 'none';
                updateLastUpdated();
                loadTodaysSnapshots(); // Load existing snapshots
            }, 1000);
        });


        // Auto-refresh weather data every 5 minutes
        setInterval(() => {
            // Refresh active weather layers
            activeWeatherLayers.forEach(layerType => {
                if (layerType === 'precipitation') {
                    map.removeLayer(precipitationLayer);
                    precipitationLayer.addTo(map);
                } else {
                    map.removeLayer(weatherLayers[layerType]);
                    weatherLayers[layerType].addTo(map);
                }
            });
            updateLastUpdated();
        }, 300000);// 5 minutes


        console.log('🗺️ Enhanced Maps Management loaded successfully!');


    </script>

    <script>
        const radios = document.querySelectorAll('.weather-radio');

        radios.forEach(btn => {
            btn.addEventListener('click', () => {
                const layerType = btn.dataset.layer;

                if (btn.classList.contains('bg-orange-200') || btn.classList.contains('bg-blue-200')) {
                    // Remove layer
                    btn.classList.remove('bg-orange-200', 'bg-blue-200', 'text-white');
                    if (btn.dataset.layer === 'temp') {
                        btn.classList.add('text-orange-700', 'bg-orange-50');
                    } else {
                        btn.classList.add('text-blue-700', 'bg-blue-50');
                    }
                    activeWeatherLayers.delete(layerType);

                    if (layerType === 'precipitation') {
                        map.removeLayer(precipitationLayer);
                    } else {
                        map.removeLayer(weatherLayers[layerType]);
                    }
                } else {
                    // Remove active from all first
                    radios.forEach(b => {
                        b.classList.remove('bg-orange-200', 'bg-blue-200', 'text-white');
                        if (b.dataset.layer === 'temp') {
                            b.classList.add('text-orange-700', 'bg-orange-50');
                        } else {
                            b.classList.add('text-blue-700', 'bg-blue-50');
                        }
                    });

                    // Add active to clicked
                    if (layerType === 'temp') {
                        btn.classList.remove('text-orange-700', 'bg-orange-50');
                        btn.classList.add('bg-orange-200', 'text-white');
                    } else if (layerType === 'precipitation') {
                        btn.classList.remove('text-blue-700', 'bg-blue-50');
                        btn.classList.add('bg-blue-200', 'text-white');
                    }

                    // Clear all active layers first
                    activeWeatherLayers.forEach(layer => {
                        if (layer === 'precipitation') {
                            map.removeLayer(precipitationLayer);
                        } else {
                            map.removeLayer(weatherLayers[layer]);
                        }
                    });
                    activeWeatherLayers.clear();

                    // Add the selected layer
                    activeWeatherLayers.add(layerType);
                    if (layerType === 'precipitation') {
                        precipitationLayer.addTo(map);
                    } else {
                        weatherLayers[layerType].addTo(map);
                    }
                }

                updateLastUpdated();
            });
        });
    </script>
@endpush