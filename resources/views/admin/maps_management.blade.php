@extends('layouts.app')

@section('title', 'Maps Management')
@section('header', 'Maps Management')

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
    
<div id="weatherPanel" class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border p-4 max-w-sm z-20 hidden">
    <!-- Content will be injected dynamically -->
</div>


    <!-- Header Controls (Bottom-Right Overlay) -->
  <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border p-4 flex flex-col gap-4 z-10 max-w-xs max-h-[60vh] overflow-y-auto">
    <!-- Map Scope -->
    <div class="flex flex-col gap-2">
        <label for="mapScope" class="text-sm font-medium text-gray-700">Map Scope:</label>
        <select id="mapScope" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            <option value="bukidnon" selected>Bukidnon</option>
            <option value="all">Philippines</option>
        </select>
    </div>

    <!-- Map Style -->
    <div class="flex flex-col gap-2">
        <label for="mapStyle" class="text-sm font-medium text-gray-700">Map Style:</label>
        <select id="mapStyle" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
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
            class="weather-radio flex items-center gap-2 px-4 py-2 rounded-full border border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100 transition-all duration-200 active:bg-purple-200"
            data-layer="storm">
            🌩️ Storms
        </button>
    </div>
</div>
</div>



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
                <span>Precipitation & Wind</span>
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

document.getElementById('mapScope').addEventListener('change', function() {
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

    // Remove previous marker
    if (clickMarker) {
        map.removeLayer(clickMarker);
    }

clickMarker = L.marker([lat, lng], {
    icon: L.divIcon({
        html: `
            <div class="flex flex-col items-center">
                <!-- Circle head -->
                <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white"></div>
                <!-- Sharp pointer -->
                <div class="w-0 h-0 border-l-4 border-r-4 border-t-6 border-l-transparent border-r-transparent border-t-blue-500"></div>
            </div>
        `,
        className: '', // remove extra default classes
        iconSize: [16, 24], // adjust for pointer size
        iconAnchor: [8, 24] // bottom tip is the anchor
    })
}).addTo(map);

    // Show loading in panel
    const weatherPanel = document.getElementById('weatherPanel');
    weatherPanel.innerHTML = `
        <div class="flex justify-center items-center p-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-2 text-gray-600 font-medium">Loading weather...</span>
        </div>
    `;
    weatherPanel.classList.remove('hidden');

    try {
        const weatherData = await fetchWeatherData(lat, lng);
        const popupContent = createWeatherPopup(weatherData, lat, lng);

        // Populate panel with weather info
        weatherPanel.innerHTML = popupContent;

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
                        Unable to fetch weather data. Please check your internet connection or try again later.
                    </div>
                </div>
            </div>
        `;
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


function createWeatherPopup(weatherData, lat, lng) {
    const temp = Math.round(weatherData.main.temp);
    const feelsLike = Math.round(weatherData.main.feels_like);
    const rain = weatherData.rain?.['1h'] || 0; // Rain in mm
    const locationName = weatherData.name || 'Unknown Location';
    const country = weatherData.sys?.country || '';
    const weatherDesc = weatherData.weather?.[0]?.description || 'No data';
    const weatherIcon = weatherData.weather?.[0]?.icon || '01d';

    // Determine storm status based on rainfall only
    let stormStatus = 'None';
    let stormColor = 'text-green-600';

    if (rain > 10) {
        stormStatus = 'Severe';
        stormColor = 'text-red-600';
    } else if (rain > 3) {
        stormStatus = 'Moderate';
        stormColor = 'text-orange-600';
    } else if (rain > 0) {
        stormStatus = 'Light';
        stormColor = 'text-yellow-600';
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

                <div class="bg-blue-50 p-2 rounded">
                    <div class="text-xs text-blue-600 font-medium">STORM STATUS</div>
                    <div class="text-lg font-bold ${stormColor}">${stormStatus}</div>
                    <div class="text-xs text-blue-500">${rain} mm rain</div>
                </div>
            </div>

            <!-- Save Weather Data Button -->
            <button id="saveWeatherBtn" onclick="saveWeatherData('${lat}', '${lng}', ${JSON.stringify(weatherData).replace(/'/g, "&apos;")})" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 mb-3 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Save Weather Data
            </button>

            <div class="mt-3 pt-3 border-t border-gray-200 text-xs text-gray-500 space-y-1">
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
    `;
}

// Function to save weather data to database
async function saveWeatherData(lat, lng, weatherData) {
    const saveBtn = document.getElementById('saveWeatherBtn');
    const originalContent = saveBtn.innerHTML;
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = `
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
        Saving...
    `;

    try {
        // Prepare data for API
        const postData = {
            latitude: parseFloat(lat),
            longitude: parseFloat(lng),
            location_name: weatherData.name || `Location (${lat}, ${lng})`,
            temperature: weatherData.main.temp,
            feels_like: weatherData.main.feels_like,
            humidity: weatherData.main.humidity,
            pressure: weatherData.main.pressure,
            wind_speed: weatherData.wind?.speed || 0,
            wind_direction: weatherData.wind?.deg ? weatherData.wind.deg.toString() : '0',
            cloudiness: weatherData.clouds?.all || 0,
            precipitation: weatherData.rain?.['1h'] || weatherData.snow?.['1h'] || 0,
            weather_main: weatherData.weather[0].main,
            weather_desc: weatherData.weather[0].description,
            weather_icon: weatherData.weather[0].icon,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };

        const response = await fetch('/weather/store-snapshot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(postData)
        });

        const result = await response.json();

        if (result.success) {
            // Success feedback
            saveBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Saved Successfully!
            `;
            saveBtn.className = 'w-full bg-green-600 text-white font-medium py-2 px-4 rounded-lg mb-3 flex items-center justify-center gap-2';
            
            // Show success message
            showNotification('Weather data saved successfully!', 'success');
            
            setTimeout(() => {
                saveBtn.innerHTML = originalContent;
                saveBtn.className = 'w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 mb-3 flex items-center justify-center gap-2';
                saveBtn.disabled = false;
            }, 2000);

        } else {
            throw new Error(result.message || 'Failed to save weather data');
        }

    } catch (error) {
        console.error('Error saving weather data:', error);
        
        // Error feedback
        saveBtn.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Save Failed
        `;
        saveBtn.className = 'w-full bg-red-600 text-white font-medium py-2 px-4 rounded-lg mb-3 flex items-center justify-center gap-2';
        
        // Show error message
        showNotification(error.message || 'Failed to save weather data. Please try again.', 'error');
        
        setTimeout(() => {
            saveBtn.innerHTML = originalContent;
            saveBtn.className = 'w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 mb-3 flex items-center justify-center gap-2';
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

// Function to display existing snapshots on map
function displaySnapshotsOnMap(snapshots) {
    // Clear existing snapshot markers
    if (window.snapshotMarkers) {
        window.snapshotMarkers.forEach(marker => map.removeLayer(marker));
    }
    window.snapshotMarkers = [];

    snapshots.forEach(snapshot => {
        const marker = L.marker([snapshot.latitude, snapshot.longitude], {
            icon: L.divIcon({
                html: `
                    <div class="flex flex-col items-center">
                        <div class="w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                            <span class="text-white text-xs font-bold">${Math.round(snapshot.temperature)}°</span>
                        </div>
                        <div class="w-0 h-0 border-l-3 border-r-3 border-t-4 border-l-transparent border-r-transparent border-t-green-500"></div>
                    </div>
                `,
                className: '',
                iconSize: [24, 32],
                iconAnchor: [12, 32]
            })
        }).addTo(map);

        marker.bindPopup(`
            <div class="p-3">
                <h3 class="font-bold text-gray-800 mb-2">${snapshot.location}</h3>
                <div class="text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Time:</span>
                        <span class="font-medium capitalize">${snapshot.snapshot_time}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Temperature:</span>
                        <span class="font-medium text-orange-600">${snapshot.temperature}°C</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Storm Status:</span>
                        <span class="font-medium capitalize">${snapshot.storm_status}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Saved:</span>
                        <span class="font-medium text-green-600">${new Date(snapshot.created_at).toLocaleTimeString()}</span>
                    </div>
                </div>
            </div>
        `);

        window.snapshotMarkers.push(marker);
    });
}

// Load existing snapshots when map is ready
map.whenReady(function() {
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


console.log('🗺️ Enhanced Maps Management loaded successfully!');
</script>
<script>
const radios = document.querySelectorAll('.weather-radio');

radios.forEach(btn => {
    btn.addEventListener('click', () => {
        const layerType = btn.dataset.layer;
        
        if (btn.classList.contains('bg-orange-200') || btn.classList.contains('bg-purple-200')) {
            // Remove layer
            btn.classList.remove('bg-orange-200', 'bg-purple-200', 'text-white');
            btn.classList.add('text-orange-700', 'bg-orange-50');
            activeWeatherLayers.delete(layerType);
            
            if (layerType === 'storm') {
                map.removeLayer(stormLayer);
            } else {
                map.removeLayer(weatherLayers[layerType]);
            }
        } else {
            // Remove active from all first
            radios.forEach(b => {
                b.classList.remove('bg-orange-200', 'bg-purple-200', 'text-white');
                if (b.dataset.layer === 'temp') {
                    b.classList.add('text-orange-700', 'bg-orange-50');
                } else {
                    b.classList.add('text-purple-700', 'bg-purple-50');
                }
            });
            
            // Add active to clicked
            if (layerType === 'temp') {
                btn.classList.remove('text-orange-700', 'bg-orange-50');
                btn.classList.add('bg-orange-200', 'text-white');
            } else if (layerType === 'storm') {
                btn.classList.remove('text-purple-700', 'bg-purple-50');
                btn.classList.add('bg-purple-200', 'text-white');
            }
            
 
            activeWeatherLayers.forEach(layer => {
                if (layer === 'storm') {
                    map.removeLayer(stormLayer);
                } else {
                    map.removeLayer(weatherLayers[layer]);
                }
            });
            activeWeatherLayers.clear();
            
   
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
</script>
@endpush