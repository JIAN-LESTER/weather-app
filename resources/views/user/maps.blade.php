@extends('layouts.app')

@section('title', 'Maps')
@section('header', 'Maps')

@section('content')

<div class="relative bg-white rounded-lg shadow-lg border overflow-hidden">

    <div id="mapLoader" class="absolute inset-0 bg-gray-50 flex items-center justify-center z-20">
        <div class="flex items-center gap-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-600 font-medium">Loading map...</span>
        </div>
    </div>
    

    <div id="map" class="h-[80vh] w-full z-0"></div>
    
<div id="weatherPanel" class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border p-4 max-w-sm z-20 hidden">

</div>



    <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border z-10">
  
        <button id="controlsToggle" class="w-full p-3 flex items-center justify-between text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors border-b border-gray-200">
            <span class="flex items-center gap-2">
                <span>🎛️</span>
                <span>Map Controls</span>
            </span>
            <svg id="controlsChevron" class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>


        <div id="controlsContent" class="transition-all duration-300 overflow-hidden max-w-xs">
            <div class="p-4 flex flex-col gap-4 max-h-[50vh] overflow-y-auto">
             
                <div class="flex flex-col gap-2">
                    <label for="mapScope" class="text-sm font-medium text-gray-700">Map Scope:</label>
                    <select id="mapScope" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors w-full">
                        <option value="bukidnon" selected>Bukidnon</option>
                        <option value="all">Philippines</option>
                    </select>
                </div>

           
                <div class="flex flex-col gap-2">
                    <label for="mapStyle" class="text-sm font-medium text-gray-700">Map Style:</label>
                    <select id="mapStyle" class="border border-gray-300 rounded-md px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="light" selected>Light Mode</option>
                        <option value="dark">Dark Mode</option>
                        <option value="satellite">Satellite</option>
                    </select>
                </div>

      
                <div class="flex flex-col gap-2">
                    <span class="text-sm font-medium text-gray-700">Weather Layers:</span>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            class="weather-radio flex items-center gap-2 px-4 py-2 rounded-full border border-orange-200 text-orange-700 bg-orange-50 hover:bg-orange-100 transition-all duration-200"
                            data-layer="temp">
                            🌡️ Temperature
                        </button>

                        <button 
                            class="weather-radio flex items-center gap-2 px-4 py-2 rounded-full border border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100 transition-all duration-200"
                            data-layer="storm">
                            🌩️ Storms
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>




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


@keyframes pulse-dot {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
}

.animate-pulse-dot {
    animation: pulse-dot 2s infinite;
}


.controls-collapsed {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
}

.controls-expanded {
    max-height: 400px;
    opacity: 1;
}

#controlsToggle:hover {
    background: rgba(249, 250, 251, 0.8);
}
</style>

<script>
const openWeatherKey = "{{ env('OPENWEATHER_API_KEY') }}";


const bukidnonBounds = L.latLngBounds([7.2, 124.2], [8.6, 125.9]);
const philippinesBounds = L.latLngBounds([4.0, 115.5], [21.5, 128.0]);


const map = L.map('map', {
    zoomControl: false,
    attributionControl: false
}).setView([7.9, 125.1], 10);


L.control.zoom({
    position: 'topright'
}).addTo(map);


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


let currentBaseLayer = baseLayers.light;
currentBaseLayer.addTo(map);


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


const stormLayer = L.layerGroup([weatherLayers.precipitation, weatherLayers.wind]);


let activeWeatherLayers = new Set();


map.setMaxBounds(bukidnonBounds);
map.on('drag', function() {
    map.panInsideBounds(map.getBounds(), { animate: false });
});


map.whenReady(function() {
    setTimeout(() => {
        document.getElementById('mapLoader').style.display = 'none';
        updateLastUpdated();
    }, 1000);
});


document.getElementById('controlsToggle').addEventListener('click', function() {
    const content = document.getElementById('controlsContent');
    const chevron = document.getElementById('controlsChevron');
    
    if (content.classList.contains('controls-collapsed')) {
        content.classList.remove('controls-collapsed');
        content.classList.add('controls-expanded');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('controls-collapsed');
        content.classList.remove('controls-expanded');
        chevron.style.transform = 'rotate(0deg)';
    }
});


document.getElementById('controlsContent').classList.add('controls-expanded');

document.getElementById('mapScope').addEventListener('change', function() {
    const scope = this.value;

    if (scope === 'bukidnon') {
   
        map.setMaxBounds(null);
        map.flyTo([7.9, 125.1], 10, { duration: 1.5 });

    
        setTimeout(() => {
            map.setMaxBounds(bukidnonBounds);
        }, 1600);
    } else {
        map.setMaxBounds(null);
        map.flyTo([12.5, 122.5], 5, { duration: 1.5 });
        setTimeout(() => {
            map.setMaxBounds(philippinesBounds);
        }, 1600);
    }

    updateLastUpdated();
});


document.getElementById('mapStyle').addEventListener('change', function() {
    const style = this.value;
    

    map.removeLayer(currentBaseLayer);
    

    currentBaseLayer = baseLayers[style];
    currentBaseLayer.addTo(map);
    
    updateLastUpdated();
});


document.querySelectorAll('.weather-radio').forEach(btn => {
    btn.addEventListener('click', function() {
        const layerType = this.dataset.layer;
        
      
        const isActive = this.classList.contains('bg-orange-200') || this.classList.contains('bg-purple-200');
        
        if (isActive) {
        
            if (layerType === 'temp') {
                this.classList.remove('bg-orange-200', 'text-white');
                this.classList.add('bg-orange-50', 'text-orange-700');
            } else if (layerType === 'storm') {
                this.classList.remove('bg-purple-200', 'text-white');
                this.classList.add('bg-purple-50', 'text-purple-700');
            }
            
            activeWeatherLayers.delete(layerType);
            
         
            if (layerType === 'storm') {
                map.removeLayer(stormLayer);
            } else {
                map.removeLayer(weatherLayers[layerType]);
            }
        } else {
          
            if (layerType === 'temp') {
                this.classList.remove('bg-orange-50', 'text-orange-700');
                this.classList.add('bg-orange-200', 'text-white');
            } else if (layerType === 'storm') {
                this.classList.remove('bg-purple-50', 'text-purple-700');
                this.classList.add('bg-purple-200', 'text-white');
            }
            
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


function updateLastUpdated() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
 
    const updateElement = document.getElementById('lastUpdate');
    if (updateElement) {
        updateElement.textContent = timeString;
    }
}


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


addSampleMarkers();




let clickMarker = null;

map.on('click', async function(e) {
    const lat = e.latlng.lat.toFixed(4);
    const lng = e.latlng.lng.toFixed(4);


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
        const weatherData = await fetchWeatherData(lat, lng);
        const popupContent = createWeatherPopup(weatherData, lat, lng);


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
    const rain = weatherData.rain?.['1h'] || 0; 
    const locationName = weatherData.name || 'Unknown Location';
    const country = weatherData.sys?.country || '';
    const weatherDesc = weatherData.weather?.[0]?.description || 'No data';
    const weatherIcon = weatherData.weather?.[0]?.icon || '01d';


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


setInterval(() => {

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
}, 300000); 

console.log('🗺️ Enhanced Maps Management loaded successfully!');
console.log('Weather layers configured:', Object.keys(weatherLayers));
console.log('OpenWeatherMap API Key configured:', !!openWeatherKey);
</script>
@endpush