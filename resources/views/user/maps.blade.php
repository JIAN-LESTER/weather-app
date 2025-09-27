@extends('layouts.app')

@section('title', 'Maps')
@section('header', 'Maps')

{{-- These sections make the layout fullscreen for maps --}}
@section('fullscreen', 'relative')
@section('header-class', 'absolute top-0 left-0 right-0')
@section('main-class', 'h-full pt-20 relative')

@section('content')
    <div class="h-full relative">
        <!-- Map Loading Indicator -->
        <div id="mapLoader" class="absolute inset-0 bg-gray-50 flex items-center justify-center z-20">
            <div class="flex items-center gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-600 font-medium">Loading map...</span>
            </div>
        </div>

        <!-- Map - Full height -->
        <div id="map" class="h-full w-full z-0"></div>

        <!-- Weather Panel - Now positioned on the left -->
        <div id="weatherPanel"
            class="absolute top-4 left-4 bg-white/95 backdrop-blur-sm rounded-lg shadow-xl  max-w-md z-20 hidden max-h-[80vh] overflow-y-auto">
            <!-- Content will be injected dynamically -->
        </div>

        <!-- Weather Layer Info Modal -->
        <div id="weatherLayerModal"
            class="fixed inset-0 bg-black/75 backdrop-blur-sm z-30 hidden flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Header with gray-800 background -->
                <div class="p-4 bg-gray-800 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h3 id="modalTitle" class="text-xl font-bold text-white"></h3>
                        <button onclick="closeWeatherModal()"
                            class="text-white/80 hover:text-white text-2xl">&times;</button>
                    </div>
                </div>

                <!-- Content with white background -->
                <div class="p-6 bg-white rounded-b-2xl">
                    <div id="modalContent" class="space-y-4">
                        <!-- Modal content will be injected here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- All Controls - Bottom Right -->
        <div class="absolute bottom-4 right-4 z-10">
            <div class="bg-white/95 backdrop-blur-sm rounded-lg shadow-lg p-3 space-y-3">
                <!-- Map Style -->
                <select id="mapStyle"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
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
                <div class="flex items-center gap-4">
                    <!-- Temperature -->
                    <label class="flex items-center gap-2 cursor-pointer group" title="Temperature">
                        <span class="text-base">🌡️Temperature</span>
                        <input type="checkbox" class="sr-only peer weather-switch" data-layer="temp">
                        <div
                            class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 relative">
                        </div>
                    </label>

                    <!-- Precipitation -->
                    <label class="flex items-center gap-2 cursor-pointer group" title="Rain & Clouds">
                        <span class="text-base">🌧️Precipitation</span>
                        <input type="checkbox" class="sr-only peer weather-switch" data-layer="precipitation">
                        <div
                            class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 relative">
                        </div>
                    </label>

                    <!-- Wind -->
                    <label class="flex items-center gap-2 cursor-pointer group" title="Wind">
                        <span class="text-base">💨Wind</span>
                        <input type="checkbox" class="sr-only peer weather-switch" data-layer="wind">
                        <div
                            class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 relative">
                        </div>
                    </label>

                    <!-- Humidity -->

                </div>
            </div>
        </div>
        <!-- Enhanced Map Legend -->
        <div class="absolute bottom-4 left-4 bg-gray-100 backdrop-blur-sm rounded-lg shadow-lg p-4 max-w-lg z-10">


            <!-- Inline Legend -->
            <div id="basicLegend" class="flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div
                        class="w-6 h-4 bg-gradient-to-r from-blue-400 via-green-400 via-yellow-400 to-red-400 rounded opacity-70">
                    </div>
                    <span class="text-gray-700">Temperature</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-4 bg-gradient-to-r from-transparent via-blue-300 to-purple-600 rounded opacity-60">
                    </div>
                    <span class="text-gray-700">Precipitation</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-4 bg-gradient-to-r from-green-300 to-red-400 rounded opacity-60"></div>
                    <span class="text-gray-700">Wind Speed</span>
                </div>

            </div>


        </div>

    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        /* Enhanced map controls styling */
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
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(229, 231, 235, 0.8);
            padding: 0;
        }

        .leaflet-popup-tip {
            background: rgba(255, 255, 255, 0.98);
        }

        .weather-popup .leaflet-popup-content {
            margin: 0 !important;
            width: auto !important;
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

        /* Weather switch animation */
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

        /* Loading indicator for switches */
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
    </style>

    <script>
        const openWeatherKey = "{{ env('OPENWEATHER_API_KEY') }}";
        const thunderforestKey = "{{ env('THUNDERFOREST_MAPS_API_KEY') }}";

        // Initialize map focused on Bukidnon but without restrictive bounds
        const map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([7.9, 125.1], 10);

        // Add custom zoom control

        // Enhanced base layers with Thunderforest maps
        const baseLayers = {
            landscape: L.tileLayer(`https://tile.thunderforest.com/landscape/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; <a href="https://www.thunderforest.com/">Thunderforest</a>, &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 22
            }),
            outdoors: L.tileLayer(`https://tile.thunderforest.com/outdoors/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; Thunderforest, &copy; OpenStreetMap contributors',
                maxZoom: 22
            }),
            transport: L.tileLayer(`https://tile.thunderforest.com/transport/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; Thunderforest, &copy; OpenStreetMap contributors',
                maxZoom: 22
            }),
            transportDark: L.tileLayer(`https://tile.thunderforest.com/transport-dark/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; Thunderforest, &copy; OpenStreetMap contributors',
                maxZoom: 22
            }),
            spinalMap: L.tileLayer(`https://tile.thunderforest.com/spinal-map/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; Thunderforest, &copy; OpenStreetMap contributors',
                maxZoom: 22
            }),
            pioneer: L.tileLayer(`https://tile.thunderforest.com/pioneer/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; Thunderforest, &copy; OpenStreetMap contributors',
                maxZoom: 22
            }),
            mobileAtlas: L.tileLayer(`https://tile.thunderforest.com/mobile-atlas/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; Thunderforest, &copy; OpenStreetMap contributors',
                maxZoom: 22
            }),
            neighbourhood: L.tileLayer(`https://tile.thunderforest.com/neighbourhood/{z}/{x}/{y}.png?apikey=${thunderforestKey}`, {
                attribution: '&copy; Thunderforest, &copy; OpenStreetMap contributors',
                maxZoom: 22
            })
        };

        // Start with Thunderforest Landscape
        let currentBaseLayer = baseLayers.landscape;
        currentBaseLayer.addTo(map);

        // Enhanced weather overlay layers
        const weatherLayers = {
            temp: L.tileLayer(`https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 1.0,
                attribution: 'Weather data © OpenWeatherMap'
            }),
            precipitation: L.layerGroup([
                L.tileLayer(`https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                    opacity: 1.0,
                    attribution: 'Weather data © OpenWeatherMap'
                }),
                L.tileLayer(`https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                    opacity: 1.0,
                    attribution: 'Weather data © OpenWeatherMap'
                })
            ]),
            wind: L.tileLayer(`https://tile.openweathermap.org/map/wind_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
                opacity: 1.0,
                attribution: 'Weather data © OpenWeatherMap'
            }),

        };

        // Track active weather layers
        let activeWeatherLayers = new Set();

        // Weather layer information
   const weatherLayerInfo = {
    temp: {
        title: "Temperature",
        description: "Shows real-time air temperature across the map, helping identify hot and cold zones at a glance. Useful for tracking heatwaves, cooler spots, or sudden temperature shifts.",
        details: [
            "Displays temperature in Celsius",
            "Color scale: Blue (cold) → Red (hot)",
            "Updates every 10 minutes",
            "Range: -10°C to 40°C"
        ],
        tips: "Redder shades mean hotter areas, while blue indicates cooler regions."
    },
    precipitation: {
        title: "Precipitation & Clouds",
        description: "Displays current rainfall intensity and cloud coverage. This helps you see where rain is falling and how thick the surrounding clouds are.",
        details: [
            "Shows rainfall in mm/hour",
            "Includes cloud density overlay",
            "Purple/Blue = rainfall intensity",
            "White/Gray = cloud cover"
        ],
        tips: "Dark purple means heavy rain, light blue suggests drizzle, and gray patches show cloudy skies."
    },
    wind: {
        title: "Wind Speed ",
        description: "Visualizes wind speed and direction, showing how air masses move across the region. Helpful for spotting strong gusts or calm areas.",
        details: [
            "Wind speed in meters/second",
            "Streamlines = wind direction",
            "Color strength = wind speed",
            "Updates from weather stations"
        ],
        tips: "Red/orange shows strong winds, green means calmer zones."
    }
};



        // Hide loader when map is ready
        map.whenReady(function () {
            setTimeout(() => {
                document.getElementById('mapLoader').style.display = 'none';
                updateLastUpdated();
                loadTodaysSnapshots();
            }, 1000);
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

        // Weather layer switch functionality
        const weatherSwitches = document.querySelectorAll('.weather-switch');

        weatherSwitches.forEach(switchElement => {
            switchElement.addEventListener('change', () => {
                const layerType = switchElement.dataset.layer;
                const switchContainer = switchElement.nextElementSibling;

                if (switchElement.checked) {
                    // Show layer info modal first
                    showWeatherLayerModal(layerType);

                    // Add layer with loading effect
                    switchContainer.classList.add('switch-loading');

                    setTimeout(() => {
                        switchContainer.classList.remove('switch-loading');
                        activeWeatherLayers.add(layerType);
                        weatherLayers[layerType].addTo(map);
                        updateActiveLayerCount();
                    }, 800);
                } else {
                    // Remove layer
                    switchContainer.classList.add('switch-loading');

                    setTimeout(() => {
                        switchContainer.classList.remove('switch-loading');
                        activeWeatherLayers.delete(layerType);
                        map.removeLayer(weatherLayers[layerType]);
                        updateActiveLayerCount();
                    }, 500);
                }

                updateLastUpdated();
            });
        });

        // Weather layer modal functions
 function showWeatherLayerModal(layerType) {
    const modal = document.getElementById('weatherLayerModal');
    const title = document.getElementById('modalTitle');
    const content = document.getElementById('modalContent');

    const info = weatherLayerInfo[layerType];

    title.textContent = info.title;
    content.innerHTML = `
        <div class="space-y-4 text-center">
            <p class="text-gray-700 leading-relaxed text-lg">${info.description}</p>

            <div class="flex justify-center mt-4">
                <button onclick="closeWeatherModal()" 
                        class="px-8 py-3 bg-gray-800 text-white font-semibold rounded-full hover:scale-105 hover:shadow-lg transition-all">
                    OK
                </button>
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
}

        function closeWeatherModal() {
            document.getElementById('weatherLayerModal').classList.add('hidden');
        }

        // Click outside modal to close
        document.getElementById('weatherLayerModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeWeatherModal();
            }
        });

        // Legend toggle functionality
        function toggleLegendDetails() {
            const basicLegend = document.getElementById('basicLegend');
            const detailedLegend = document.getElementById('detailedLegend');
            const toggleText = document.getElementById('legendToggle');

            if (detailedLegend.classList.contains('hidden')) {
                basicLegend.classList.add('hidden');
                detailedLegend.classList.remove('hidden');
                toggleText.textContent = 'Show Less';
            } else {
                detailedLegend.classList.add('hidden');
                basicLegend.classList.remove('hidden');
                toggleText.textContent = 'Show Details';
            }
        }

        // Close weather panel function
        function closeWeatherPanel() {
            document.getElementById('weatherPanel').classList.add('hidden');

            // Remove click marker
            if (clickMarker) {
                map.removeLayer(clickMarker);
                clickMarker = null;
            }
        }

        // Update active layer count
        function updateActiveLayerCount() {
            document.getElementById('activeLayerCount').textContent = activeWeatherLayers.size;
        }

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
                                        <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white animate-pulse"></div>
                                        <div class="w-0 h-0 border-l-4 border-r-4 border-t-6 border-l-transparent border-r-transparent border-t-blue-500"></div>
                                    </div>
                                `,
                    className: '',
                    iconSize: [16, 24],
                    iconAnchor: [8, 24]
                })
            }).addTo(map);

            // Calculate optimal view for popup visibility
            const mapSize = map.getSize();
            const popupWidth = 320; // Approximate popup width
            const popupHeight = 600; // Approximate popup height
            const padding = 20;

            // Get the pixel position of the clicked point
            const clickPixel = map.latLngToContainerPoint([lat, lng]);

            // Check if we need to pan to show popup on the left
            let needsPan = false;

            // If popup would be cut off on the left side
            if (clickPixel.x < popupWidth + padding) {
                // Pan right to make room
                const pixelOffset = (popupWidth + padding) - clickPixel.x;
                const latLngOffset = map.containerPointToLatLng([clickPixel.x + pixelOffset, clickPixel.y]);
                needsPan = true;

                setTimeout(() => {
                    map.panTo(latLngOffset, { animate: true, duration: 0.5 });
                }, 100);
            }

            const weatherPanel = document.getElementById('weatherPanel');
            weatherPanel.innerHTML = `
                            <div class="flex justify-between items-center p-4 border-b border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                    <span class="text-gray-600 font-medium">Loading weather data...</span>
                                </div>
                                <button onclick="closeWeatherPanel()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
                            </div>
                            <div class="p-4">
                                <div class="text-center text-gray-500">Please wait...</div>
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

                // Render enhanced panel
                weatherPanel.innerHTML = createEnhancedWeatherPopup(currentWeather, fullDayForecast, lat, lng);

            } catch (error) {
                console.error('Error fetching weather data:', error);
                weatherPanel.innerHTML = `
                                <div class="flex justify-between items-center p-4 border-b border-gray-200">
                                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                        <span class="text-red-500">⚠️</span>
                                        Weather Data Unavailable
                                    </h3>
                                    <button onclick="closeWeatherPanel()" class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
                                </div>
                                <div class="p-4">
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Latitude:</span>
                                            <span class="font-mono font-medium">${lat}°</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Longitude:</span>
                                            <span class="font-mono font-medium">${lng}°</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-3 p-3 bg-gray-50 rounded-lg">
                                            Unable to fetch weather data. Please try another location.
                                        </div>
                                    </div>
                                </div>
                            `;
            }
        });

        // Fetch current weather data
        async function fetchCurrentWeatherData(lat, lng) {
            const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=${openWeatherKey}&units=metric`);
            if (!response.ok) throw new Error('Weather API request failed');
            return await response.json();
        }

        // Fetch full day forecast data
        async function fetchFullDayForecastData(lat, lng) {
            const response = await fetch(`https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lng}&appid=${openWeatherKey}&units=metric`);
            if (!response.ok) throw new Error('Forecast API request failed');
            return await response.json();
        }

        // Create enhanced weather popup
        function createEnhancedWeatherPopup(currentData, forecastData, lat, lng) {
            const current = currentData;
            const location = current.name || `${lat}°, ${lng}°`;
            const temp = Math.round(current.main.temp);
            const feelsLike = Math.round(current.main.feels_like);
            const description = current.weather[0].description;
            const icon = current.weather[0].icon;
            const humidity = current.main.humidity;
            const pressure = current.main.pressure;
            const windSpeed = current.wind?.speed || 0;
            const windDir = current.wind?.deg || 0;

            // Process hourly forecast (next 24 hours)
            const now = new Date();
            const next24Hours = forecastData.list.slice(0, 6).map(item => ({
                time: new Date(item.dt * 1000),
                temp: Math.round(item.main.temp),
                icon: item.weather[0].icon,
                description: item.weather[0].description,
                humidity: item.main.humidity,
                windSpeed: item.wind?.speed || 0,
                rainAmount: item.rain?.["3h"] || 0,
                rainChance: Math.round((item.pop || 0) * 100)
            }));

            // Process daily forecast (next 5 days)
            const dailyForecast = [];
            const seenDates = new Set();

            forecastData.list.forEach(item => {
                const date = new Date(item.dt * 1000);
                const dateKey = date.toDateString();

                if (!seenDates.has(dateKey) && dailyForecast.length < 5) {
                    seenDates.add(dateKey);
                    dailyForecast.push({
                        date: date,
                        temp: Math.round(item.main.temp),
                        icon: item.weather[0].icon,
                        description: item.weather[0].description,
                        humidity: item.main.humidity,
                        rainAmount: item.rain?.["3h"] || 0,
                        rainChance: Math.round((item.pop || 0) * 100)
                    });
                }
            });

            return `
                    <div class="bg-white rounded-lg overflow-hidden" style="width: 320px;">
                        <!-- Header -->
                        <div class="flex justify-between items-center p-2 bg-gray-800 text-white">
                            <div class="flex items-center gap-2">
                                <img src="https://openweathermap.org/img/wn/${icon}.png" 
                                    alt="${description}" 
                                    class="w-8 h-8">
                                <div>
                                    <h3 class="font-bold text-lg">${location}</h3>

                                </div>
                            </div>
                            <button onclick="closeWeatherPanel()" 
                                    class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
                        </div>

                        <!-- Stats Grid -->
                        <div class="p-2 border-b border-gray-100">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded p-2 border border-orange-200">
                                    <div class="text-xs font-medium text-orange-700">🌡️ TEMP</div>
                                    <div class="text-xl font-bold text-orange-900">${temp}°C</div>
                                    <div class="text-xs text-orange-600">Feels ${feelsLike}°</div>
                                </div>

                                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded p-2 border border-blue-200">
                                    <div class="text-xs font-medium text-blue-700">🌧️ RAIN</div>
                                    <div class="text-xl font-bold text-blue-900">${next24Hours[0]?.rainChance || 0}%</div>
                                    <div class="text-xs text-blue-600">${next24Hours[0]?.rainAmount || 0} mm</div>
                                </div>

                                <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded p-2 border border-teal-200">
                                    <div class="text-xs font-medium text-teal-700">🌪️ WIND</div>
                                    <div class="text-xl font-bold text-teal-900">${windSpeed}</div>
                                    <div class="text-xs text-teal-600">m/s · ${windDir}°</div>
                                </div>

                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded p-2 border border-purple-200">
                                    <div class="text-xs font-medium text-purple-700">💧 HUMID</div>
                                    <div class="text-xl font-bold text-purple-900">${humidity}%</div>
                                    <div class="text-xs text-purple-600">${pressure} hPa</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="flex border-b border-gray-200">
                            <button onclick="showWeatherTab('hourly')" 
                                    id="hourlyTab"
                                    class="flex-1 px-2 py-2 text-xs font-medium border-b-2 border-blue-500 text-blue-600 bg-blue-50">
                                Hourly
                            </button>
                            <button onclick="showWeatherTab('daily')" 
                                    id="dailyTab"
                                    class="flex-1 px-2 py-2 text-xs font-medium border-b-2 border-transparent text-gray-600 hover:bg-gray-50">
                                5-Day
                            </button>
                        </div>

                        <!-- Tab Content -->
                        <div class="max-h-48 overflow-y-auto">
                            <!-- Hourly -->
                            <div id="hourlyContent" class="p-2">
                                <div class="space-y-1">
                                    ${next24Hours.map(hour => `
                                        <div class="flex items-center justify-between p-1.5 rounded bg-gray-50 hover:bg-gray-100">
                                            <div class="flex items-center gap-2">
                                                <img src="https://openweathermap.org/img/wn/${hour.icon}.png" 
                                                    alt="${hour.description}" 
                                                    class="w-6 h-6">
                                                <div class="text-xs font-medium">${hour.time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500">🌧️${hour.rainChance}%</span>
                                                <span class="font-bold text-sm">${hour.temp}°</span>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>

                            <!-- Daily -->
                            <div id="dailyContent" class="p-2 hidden">
                                <div class="space-y-1">
                                    ${dailyForecast.map((day, index) => `
                                        <div class="flex items-center justify-between p-1.5 rounded ${index === 0 ? 'bg-blue-50' : 'bg-gray-50'} hover:bg-gray-100">
                                            <div class="flex items-center gap-2">
                                                <img src="https://openweathermap.org/img/wn/${day.icon}.png" 
                                                    alt="${day.description}" 
                                                    class="w-6 h-6">
                                                <div class="text-xs font-medium">${index === 0 ? 'Today' : day.date.toLocaleDateString('en-US', { weekday: 'short' })}</div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500">🌧️${day.rainChance}%</span>
                                                <span class="font-bold text-sm">${day.temp}°</span>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-2 bg-gray-50 border-t border-gray-200">
                            <div class="text-xs text-gray-500 text-center">
                                Updated: ${new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                            </div>
                        </div>
                    </div>
                `;
        }
        // Weather tab functionality
        function showWeatherTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('#weatherPanel button[id$="Tab"]').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
                btn.classList.add('border-transparent', 'text-gray-600');
            });

            document.getElementById(`${tabName}Tab`).classList.remove('border-transparent', 'text-gray-600');
            document.getElementById(`${tabName}Tab`).classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');

            // Update content
            document.querySelectorAll('#weatherPanel div[id$="Content"]').forEach(content => {
                content.classList.add('hidden');
            });

            document.getElementById(`${tabName}Content`).classList.remove('hidden');

            // Setup layer panel switches if on map tab
            if (tabName === 'map') {
                setTimeout(setupLayerPanelSwitches, 100);
            }
        }

        // Setup layer panel switches
        function setupLayerPanelSwitches() {
            const layerSwitches = document.querySelectorAll('.layer-panel-switch');

            layerSwitches.forEach(switchElement => {
                switchElement.addEventListener('change', () => {
                    const layerType = switchElement.dataset.layer;
                    const correspondingSwitch = document.querySelector(`.weather-switch[data-layer="${layerType}"]`);

                    if (correspondingSwitch) {
                        correspondingSwitch.checked = switchElement.checked;
                        correspondingSwitch.dispatchEvent(new Event('change'));
                    }
                });
            });
        }


        // Auto-refresh weather layers every 10 minutes
        setInterval(() => {
            if (activeWeatherLayers.size > 0) {
                console.log('Refreshing weather layers...');

                // Remove and re-add active layers to refresh them
                activeWeatherLayers.forEach(layerType => {
                    map.removeLayer(weatherLayers[layerType]);

                    setTimeout(() => {
                        weatherLayers[layerType].addTo(map);
                    }, 1000);
                });

                updateLastUpdated();
            }
        }, 600000); // 10 minutes



        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Escape key to close weather panel
            if (e.key === 'Escape') {
                const weatherPanel = document.getElementById('weatherPanel');
                if (!weatherPanel.classList.contains('hidden')) {
                    closeWeatherPanel();
                }
            }

            // F11 for fullscreen
            if (e.key === 'F11') {
                e.preventDefault();
                toggleFullscreen();
            }

            // Number keys for quick layer toggles
            if (e.key >= '1' && e.key <= '4') {
                const layerKeys = ['temp', 'precipitation', 'wind'];
                const layerIndex = parseInt(e.key) - 1;

                if (layerIndex < layerKeys.length) {
                    const layerType = layerKeys[layerIndex];
                    const switchElement = document.querySelector(`.weather-switch[data-layer="${layerType}"]`);

                    if (switchElement) {
                        switchElement.checked = !switchElement.checked;
                        switchElement.dispatchEvent(new Event('change'));
                    }
                }
            }
        });

        // Initialize timestamp on load
        updateLastUpdated();

        console.log('🗺️ Enhanced Weather Map initialized successfully!');
        console.log('💡 Tip: Click anywhere on the map to get detailed weather information');
        console.log('⌨️ Keyboard shortcuts: 1-4 (toggle layers), Esc (close panel), F11 (fullscreen)');
    </script>
@endpush