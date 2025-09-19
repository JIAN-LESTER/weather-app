@extends('layouts.app')

@section('title', 'Maps')
@section('header', 'Maps')

@section('content')

<!-- Enhanced Header Controls -->
<div class="bg-white rounded-lg shadow-sm border mb-6 p-4">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Map Controls -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex items-center gap-2">
                <label for="mapScope" class="text-sm font-medium text-gray-700">Map Scope:</label>
                <select id="mapScope" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <option value="bukidnon" selected>🏔️ Bukidnon Province</option>
                    <option value="all">🇵🇭 All Philippines</option>
                </select>
            </div>
            
            <div class="flex items-center gap-2">
                <label for="mapStyle" class="text-sm font-medium text-gray-700">Map Style:</label>
                <select id="mapStyle" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <option value="light" selected>☀️ Light Mode</option>
                    <option value="dark">🌙 Dark Mode</option>
                    <option value="satellite">🛰️ Satellite</option>
                </select>
            </div>
        </div>

        <!-- Weather Controls -->
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Weather Layers:</span>
            <div class="flex flex-wrap gap-2">
                <button id="toggleTemp" class="weather-btn bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100" data-layer="temp">
                    🌡️ Temperature
                </button>
                <button id="toggleStorm" class="weather-btn bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100" data-layer="storm">
                    🌩️ Storms
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Map Container with Enhanced Styling -->
<div class="relative bg-white rounded-lg shadow-lg border overflow-hidden">
    <!-- Map Loading Indicator -->
    <div id="mapLoader" class="absolute inset-0 bg-gray-50 flex items-center justify-center z-50">
        <div class="flex items-center gap-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-600 font-medium">Loading map...</span>
        </div>
    </div>
    
    <!-- Map -->
    <div id="map" class="h-[80vh] w-full"></div>
    
    <!-- Map Info Panel -->
    <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border p-3 max-w-xs z-10">
        <div class="text-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="font-medium text-gray-800">Live Weather Data</span>
            </div>
            <div class="text-gray-600 text-xs space-y-1">
                <div>📍 Current View: <span id="currentScope" class="font-medium">Bukidnon</span></div>
                <div>🔄 Last Updated: <span id="lastUpdate">Now</span></div>
                <div>⚡ Data Source: OpenWeatherMap</div>
                <div class="mt-2 p-2 bg-blue-50 rounded text-blue-700">
                    💡 <strong>Tip:</strong> Click anywhere on the map to get detailed weather information for that location!
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="mt-6 bg-white rounded-lg shadow-sm border p-4">
    <h3 class="text-lg font-semibold text-gray-800 mb-3">🗺️ Map Legend</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="space-y-2">
            <h4 class="font-medium text-gray-700">Weather Layers</h4>
            <div class="space-y-1 text-sm text-gray-600">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-3 bg-blue-300 rounded opacity-50"></div>
                    <span>Cloud Coverage</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-3 bg-gradient-to-r from-blue-500 to-red-500 rounded opacity-50"></div>
                    <span>Temperature (°C)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-3 bg-purple-400 rounded opacity-60"></div>
                    <span>Precipitation & Wind</span>
                </div>
            </div>
        </div>
        <div class="space-y-2">
            <h4 class="font-medium text-gray-700">Map Controls</h4>
            <div class="space-y-1 text-sm text-gray-600">
                <div>🖱️ Click & drag to pan</div>
                <div>🔍 Scroll to zoom in/out</div>
                <div>📱 Touch gestures supported</div>
            </div>
        </div>
        <div class="space-y-2">
            <h4 class="font-medium text-gray-700">Coverage Areas</h4>
            <div class="space-y-1 text-sm text-gray-600">
                <div>🏔️ Bukidnon: Detailed provincial view</div>
                <div>🇵🇭 Philippines: National overview</div>
                <div>🛰️ Real-time weather updates</div>
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
    0%, 20%, 50%, 80%, 100% {
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
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
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
        opacity: 0.5,
        attribution: 'Weather data © OpenWeatherMap'
    }),
    precipitation: L.tileLayer(`https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
        opacity: 0.6,
        attribution: 'Weather data © OpenWeatherMap'
    }),
    wind: L.tileLayer(`https://tile.openweathermap.org/map/wind_new/{z}/{x}/{y}.png?appid=${openWeatherKey}`, {
        opacity: 0.5,
        attribution: 'Weather data © OpenWeatherMap'
    })
};

// Storm layer group
const stormLayer = L.layerGroup([weatherLayers.precipitation, weatherLayers.wind]);

// Track active weather layers
let activeWeatherLayers = new Set();

// Set initial bounds
map.setMaxBounds(bukidnonBounds);
map.on('drag', function() {
    map.panInsideBounds(map.getBounds(), { animate: false });
});

// Hide loader when map is ready
map.whenReady(function() {
    setTimeout(() => {
        document.getElementById('mapLoader').style.display = 'none';
        updateLastUpdated();
    }, 1000);
});

// Enhanced scope toggle
document.getElementById('mapScope').addEventListener('change', function() {
    const scope = this.value;
    const scopeDisplay = document.getElementById('currentScope');
    
    if (scope === 'bukidnon') {
        map.flyTo([7.9, 125.1], 10, { duration: 1.5 });
        map.setMaxBounds(bukidnonBounds);
        scopeDisplay.textContent = 'Bukidnon Province';
    } else {
        map.flyTo([12.5, 122.5], 6, { duration: 1.5 });
        map.setMaxBounds(philippinesBounds);
        scopeDisplay.textContent = 'All Philippines';
    }
    
    updateLastUpdated();
});

// Map style switcher
document.getElementById('mapStyle').addEventListener('change', function() {
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
    btn.addEventListener('click', function() {
        const layerType = this.dataset.layer;
        
        if (this.classList.contains('active')) {
            // Remove layer
            this.classList.remove('active');
            activeWeatherLayers.delete(layerType);
            
            if (layerType === 'storm') {
                map.removeLayer(stormLayer);
            } else {
                map.removeLayer(weatherLayers[layerType]);
            }
        } else {
            // Add layer
            this.classList.add('active');
            activeWeatherLayers.add(layerType);
            
            if (layerType === 'storm') {
                stormLayer.addTo(map);
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

map.on('click', async function(e) {
    const lat = e.latlng.lat.toFixed(4);
    const lng = e.latlng.lng.toFixed(4);
    
    // Remove previous click marker
    if (clickMarker) {
        map.removeLayer(clickMarker);
    }
    
    // Add temporary marker with loading state
    clickMarker = L.marker([lat, lng], {
        icon: L.divIcon({
            html: `<div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 bg-white"></div>`,
            className: 'custom-loading-marker',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        })
    }).addTo(map);
    
    try {
        // Fetch weather data for clicked location
        const weatherData = await fetchWeatherData(lat, lng);
        
        // Update marker with weather info
        clickMarker.setIcon(L.divIcon({
            html: `<div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-xs font-bold shadow-lg">📍</div>`,
            className: 'custom-weather-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        }));
        
        // Create detailed popup content
        const popupContent = createWeatherPopup(weatherData, lat, lng);
        clickMarker.bindPopup(popupContent, {
            maxWidth: 300,
            className: 'weather-popup'
        }).openPopup();
        
    } catch (error) {
        console.error('Error fetching weather data:', error);
        
        // Show error popup with basic location info
        clickMarker.setIcon(L.divIcon({
            html: `<div class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-xs font-bold shadow-lg">❌</div>`,
            className: 'custom-error-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        }));
        
        clickMarker.bindPopup(`
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
                        Unable to fetch weather data. Please check your internet connection or try again later.
                    </div>
                </div>
            </div>
        `).openPopup();
    }
});

// Fetch weather data from OpenWeatherMap API
async function fetchWeatherData(lat, lng) {
    if (!openWeatherKey) {
        throw new Error('OpenWeatherMap API key not configured');
    }
    
    const response = await fetch(
        `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=${openWeatherKey}&units=metric`
    );
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return await response.json();
}

// Fetch location name using reverse geocoding
async function fetchLocationName(lat, lng) {
    try {
        const response = await fetch(
            `https://api.openweathermap.org/geo/1.0/reverse?lat=${lat}&lon=${lng}&limit=1&appid=${openWeatherKey}`
        );
        
        if (response.ok) {
            const data = await response.json();
            if (data && data.length > 0) {
                const location = data[0];
                return `${location.name}${location.state ? ', ' + location.state : ''}, ${location.country}`;
            }
        }
    } catch (error) {
        console.warn('Reverse geocoding failed:', error);
    }
    
    return null;
}

// Create detailed weather popup content
function createWeatherPopup(weatherData, lat, lng) {
    const temp = Math.round(weatherData.main.temp);
    const feelsLike = Math.round(weatherData.main.feels_like);
    const humidity = weatherData.main.humidity;
    const windSpeed = Math.round(weatherData.wind?.speed * 3.6) || 0; // Convert m/s to km/h
    const windDir = weatherData.wind?.deg || 0;
    const pressure = weatherData.main.pressure;
    const visibility = weatherData.visibility ? (weatherData.visibility / 1000).toFixed(1) : 'N/A';
    const cloudiness = weatherData.clouds?.all || 0;
    const precipitation = weatherData.rain?.['1h'] || weatherData.snow?.['1h'] || 0;
    
    const locationName = weatherData.name || 'Unknown Location';
    const country = weatherData.sys?.country || '';
    const weatherDesc = weatherData.weather?.[0]?.description || 'No data';
    const weatherIcon = weatherData.weather?.[0]?.icon || '01d';
    
    // Determine storm status
    let stormStatus = 'None';
    let stormColor = 'text-green-600';
    
    if (precipitation > 5 || windSpeed > 50) {
        stormStatus = 'Severe';
        stormColor = 'text-red-600';
    } else if (precipitation > 1 || windSpeed > 30) {
        stormStatus = 'Moderate';
        stormColor = 'text-orange-600';
    } else if (precipitation > 0 || windSpeed > 15) {
        stormStatus = 'Light';
        stormColor = 'text-yellow-600';
    }
    
    // Wind direction helper
    function getWindDirection(deg) {
        const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        return directions[Math.round(deg / 22.5) % 16];
    }
    
    return `
        <div class="p-4 max-w-sm">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">${locationName}</h3>
                    ${country ? `<p class="text-xs text-gray-500">${country}</p>` : ''}
                </div>
                <img src="https://openweathermap.org/img/wn/${weatherIcon}@2x.png" 
                     alt="${weatherDesc}" class="w-12 h-12" />
            </div>
            
   
            
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="bg-red-50 p-2 rounded">
                    <div class="text-xs text-orange-600 font-medium">TEMPERATURE</div>
                    <div class="text-lg font-bold text-orange-700">${temp}°C</div>
                    <div class="text-xs text-orange-500">Feels ${feelsLike}°C</div>
                </div>
                
                <div class="bg-purple-50 p-2 rounded">
                    <div class="text-xs text-purple-600 font-medium">STORM STATUS</div>
                    <div class="text-lg font-bold ${stormColor}">${stormStatus}</div>
                    <div class="text-xs text-purple-500">${precipitation}mm rain</div>
                </div>
            </div>
            
      
            
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="text-xs text-gray-500 space-y-1">
                    <div class="flex justify-between">
                        <span>📍 Latitude:</span>
                        <span class="font-mono">${lat}°</span>
                    </div>
                    <div class="flex justify-between">
                        <span>📍 Longitude:</span>
                        <span class="font-mono">${lng}°</span>
                    </div>
                    <div class="text-center mt-2 text-blue-500">
                        Click anywhere on the map for weather data
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Auto-refresh weather data every 5 minutes
setInterval(() => {
    // Refresh active weather layers
    activeWeatherLayers.forEach(layerType => {
        if (layerType === 'storm') {
            map.removeLayer(stormLayer);
            stormLayer.addTo(map);
        } else {
            map.removeLayer(weatherLayers[layerType]);
            weatherLayers[layerType].addTo(map);
        }
    });
    updateLastUpdated();
}, 300000); // 5 minutes

// Add attribution
L.control.attribution({
    position: 'bottomright'
}).addTo(map).addAttribution('Weather data © OpenWeatherMap');

console.log('🗺️ Enhanced Maps Management loaded successfully!');
</script>
@endpush