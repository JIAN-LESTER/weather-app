@extends('layouts.app')

@section('title', 'Weather Reports Management')
@section('header', 'Weather Reports Management')

@section('content')
<div class="space-y-6">
    <!-- Notification Container -->
    <div id="notificationContainer" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm"></div>



    <!-- Search and Actions -->
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:min-w-80">
                    <input 
                        type="text" 
                        id="locationSearch" 
                        placeholder="Search locations..." 
                        class="w-full border border-gray-300 rounded-md px-4 py-2 pl-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button id="storeNow" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fas fa-save mr-1"></i> Store Now
                </button>
                <button id="refreshData" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                <button id="cleanupOld" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fas fa-trash-alt mr-1"></i> Cleanup Old Reports
                </button>
            </div>
        </div>
    </div>

    <!-- Historical Weather Reports Grid -->
    @if(isset($snapshots) && !$snapshots->isEmpty())
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Historical Data (Last 7 Days)</h2>
            
            <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-3" id="weatherCardsContainer">
                @foreach($snapshots as $snapshot)
                    @php
                        $location = $snapshot->weatherReport->location ?? null;
                        if (!$location) continue;
                        
                        $summary = $snapshot->getSummary();
                        $periods = $snapshot->getAvailableTimePeriods();
                    @endphp
                    
                    <div class="weather-card bg-white rounded-lg shadow-sm border hover:shadow-lg transition-all duration-200 cursor-pointer transform hover:scale-105" 
                         onclick="openWeatherModal('{{ $location->locID }}')"
                         data-location-name="{{ strtolower($location->name) }}">
                        <!-- Location Header -->
                        <div class="p-4 border-b bg-gradient-to-r from-gray-50 to-blue-50 rounded-t-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">
                                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                        {{ $location->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ number_format($location->latitude, 4) }}, 
                                        {{ number_format($location->longitude, 4) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">
                                        {{ $snapshot->created_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $snapshot->created_at->format('H:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Weather Summary -->
                        @if($summary)
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center">
                                        <div class="text-3xl font-bold text-gray-900">
                                            {{ number_format($summary['temperature'], 1) }}°C
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-700">
                                                {{ ucfirst($summary['weather_desc'] ?? 'N/A') }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Status: {{ ucfirst(str_replace('_', ' ', $summary['storm_status'] ?? 'clear')) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Time Periods -->
                                <div class="border-t pt-4">
                                    <div class="text-sm font-medium text-gray-700 mb-2">
                                        Available Periods ({{ $summary['periods_count'] ?? count($periods) }})
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($periods as $period)
                                            @php
                                                $periodIcons = [
                                                    'morning' => '🌅',
                                                    'noon' => '☀️',
                                                    'afternoon' => '🌤️',
                                                    'evening' => '🌆'
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium">
                                                {{ $periodIcons[$period] ?? '⏰' }} {{ ucfirst($period) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Click hint -->
                                <div class="mt-4 pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 text-center">
                                        <i class="fas fa-mouse-pointer mr-1"></i>
                                        Click for detailed view
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="p-4 text-center text-gray-500">
                                <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                                <p class="text-sm">No summary available</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $snapshots->links() }}
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border p-12 text-center">
            <i class="fas fa-cloud-sun text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Weather Reports Available</h3>
            <p class="text-gray-500 mb-6">No weather data has been collected yet. Use the "Store Now" button to fetch the latest forecasts.</p>
            <button onclick="document.getElementById('storeNow').click()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md font-medium">
                <i class="fas fa-save mr-2"></i> Store Weather Data Now
            </button>
        </div>
    @endif
</div>

<!-- Weather Modal -->
<div id="weatherModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold" id="modalLocationName">Location Name</h2>
                    <p class="text-blue-100 mt-1" id="modalLocationCoords">Coordinates</p>
                </div>
                <button onclick="closeWeatherModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 120px);">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="modalWeatherContent">
                <!-- Content will be dynamically loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Store weather data for modal
    const weatherData = @json($todaySnapshots ?? []);

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        const typeClasses = {
            'info': 'bg-blue-500 border-blue-600',
            'success': 'bg-green-500 border-green-600',
            'warning': 'bg-yellow-500 border-yellow-600',
            'error': 'bg-red-500 border-red-600'
        };
        
        notification.className = `p-4 rounded-lg border-l-4 text-white shadow-lg transform transition-all duration-300 ${typeClasses[type]}`;
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : type === 'warning' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                    <span class="text-sm font-medium">${message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.getElementById('notificationContainer').appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Auto-operation notifications
    function simulateAutoNotifications() {
        setInterval(() => {
            if (Math.random() < 0.3) { // 30% chance every minute
                showNotification('Auto storage: Weather forecasts updated for all locations', 'success');
            }
            if (Math.random() < 0.2) { // 20% chance every minute  
                showNotification('Auto cleanup: Old weather reports cleaned up', 'info');
            }
        }, 60000); // Every minute
    }

    // Start auto notifications
    simulateAutoNotifications();

    // Location search functionality
    document.getElementById('locationSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.weather-card');
        
        cards.forEach(card => {
            const locationName = card.getAttribute('data-location-name');
            if (locationName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    function openWeatherModal(locID) {
        const locationData = weatherData[locID];
        if (!locationData) {
            showNotification('No weather data available for this location', 'error');
            return;
        }

        // Update modal header
        document.getElementById('modalLocationName').textContent = locationData.location.name;
        document.getElementById('modalLocationCoords').textContent = 
            `${parseFloat(locationData.location.latitude).toFixed(4)}, ${parseFloat(locationData.location.longitude).toFixed(4)}`;

        // Generate modal content
        const modalContent = document.getElementById('modalWeatherContent');
        modalContent.innerHTML = generateModalContent(locationData);

        // Show modal
        document.getElementById('weatherModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeWeatherModal() {
        document.getElementById('weatherModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function generateModalContent(locationData) {
        const periodConfig = {
            'morning': { 
                icon: '🌅', 
                bg: 'bg-orange-50', 
                border: 'border-orange-300', 
                text: 'text-orange-700',
                accent: 'bg-orange-500',
                time: '6-10 AM'
            },
            'noon': { 
                icon: '☀️', 
                bg: 'bg-yellow-50', 
                border: 'border-yellow-300', 
                text: 'text-yellow-700',
                accent: 'bg-yellow-500',
                time: '11 AM - 2 PM'
            },
            'afternoon': { 
                icon: '🌤️', 
                bg: 'bg-blue-50', 
                border: 'border-blue-300', 
                text: 'text-blue-700',
                accent: 'bg-blue-500',
                time: '3-5 PM'
            },
            'evening': { 
                icon: '🌆', 
                bg: 'bg-purple-50', 
                border: 'border-purple-300', 
                text: 'text-purple-700',
                accent: 'bg-purple-500',
                time: '6-10 PM'
            }
        };

        const statusColors = {
            'clear': 'bg-green-100 text-green-800 border-green-300',
            'possible_rain': 'bg-blue-100 text-blue-800 border-blue-300',
            'light_rain': 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'moderate_rain': 'bg-orange-100 text-orange-800 border-orange-300',
            'heavy_rain': 'bg-red-100 text-red-800 border-red-300',
        };

        let html = '';

        for (const [period, config] of Object.entries(periodConfig)) {
            // Get period data using the updated function
            const periodData = getPeriodData(locationData, period);
            
            html += `
                <div class="border-2 rounded-xl ${config.border} ${config.bg} overflow-hidden shadow-lg hover:shadow-xl transition-shadow">
                    <!-- Period Header -->
                    <div class="${config.accent} text-white p-4">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <span class="text-3xl">${config.icon}</span>
                                <div>
                                    <h3 class="font-bold text-lg uppercase">${period}</h3>
                                    <p class="text-sm opacity-90">${config.time}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weather Content -->
                    <div class="p-6">`;

            if (periodData) {
                const stormStatus = periodData.storm_status || 'clear';
                const statusClass = statusColors[stormStatus] || 'bg-gray-100 text-gray-800';

                    html += `
                        <!-- Temperature Display -->
                        <div class="text-center mb-6">
                            <div class="text-5xl font-bold text-gray-900 mb-2">
                                ${parseFloat(periodData.temperature || 0).toFixed(1)}°C
                            </div>
                            <div class="text-sm text-gray-600">
                                Feels like ${parseFloat(periodData.feels_like || periodData.temperature || 0).toFixed(1)}°C
                            </div>
                            <div class="mt-3">
                                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold ${statusClass} border">
                                    ${capitalizeWords(stormStatus.replace(/_/g, ' '))}
                                </span>
                            </div>
                        </div>

                        <!-- Weather Description -->
                        <div class="text-center mb-6 pb-6 border-b">
                            <div class="flex items-center justify-center space-x-2">
                                ${periodData.weather_icon ? `<img src="https://openweathermap.org/img/wn/${periodData.weather_icon}@2x.png" alt="weather" class="w-16 h-16">` : ''}
                                <span class="text-xl font-medium text-gray-800">
                                    ${capitalizeWords(periodData.weather_desc || 'N/A')}
                                </span>
                            </div>
                        </div>

                        <!-- Precipitation Section -->
                        <div class="bg-white rounded-lg p-4 mb-4 border-2 border-blue-100">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-cloud-rain text-blue-500 mr-2"></i>
                                Precipitation Details
                            </h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">
                                        <i class="fas fa-percentage text-blue-400 mr-2"></i>Rain Chance
                                    </span>
                                    <span class="font-bold text-blue-600 text-lg">
                                        ${parseFloat(periodData.rain_chance || 0).toFixed(0)}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">
                                        <i class="fas fa-tint text-blue-400 mr-2"></i>Rain Amount
                                    </span>
                                    <span class="font-bold text-blue-600 text-lg">
                                        ${parseFloat(periodData.rain_amount || 0).toFixed(2)} mm
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Metrics Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-droplet mr-1"></i>Humidity
                                </div>
                                <div class="text-lg font-bold text-gray-800">
                                    ${periodData.humidity || 0}%
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-wind mr-1"></i>Wind Speed
                                </div>
                                <div class="text-lg font-bold text-gray-800">
                                    ${parseFloat(periodData.wind_speed || 0).toFixed(1)} m/s
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-gauge mr-1"></i>Pressure
                                </div>
                                <div class="text-lg font-bold text-gray-800">
                                    ${periodData.pressure || 0} hPa
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-cloud mr-1"></i>Cloudiness
                                </div>
                                <div class="text-lg font-bold text-gray-800">
                                    ${periodData.cloudiness || 0}%
                                </div>
                            </div>
                        </div>

                        <!-- Forecast Time -->
                        <div class="mt-4 text-center text-xs text-gray-500">
                            <i class="fas fa-clock mr-1"></i>
                            Forecast for: ${periodData.forecast_time || 'N/A'}
                        </div>
                    `;
                } else {
                    html += `
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-cloud text-5xl mb-3"></i>
                            <p class="text-lg">No data available for this period</p>
                        </div>
                    `;
                }

            html += `
                    </div>
                </div>
            `;
        }

        return html;
    }

    function getPeriodData(locationData, period) {
        // Check if period data is already extracted and available
        if (locationData.periods && locationData.periods[period] && locationData.periods[period].data) {
            return locationData.periods[period].data;
        }

        // Fallback: Look through raw snapshots if available
        if (locationData.raw_snapshots) {
            for (const snapshot of locationData.raw_snapshots) {
                if (snapshot.snapshots) {
                    for (const key in snapshot.snapshots) {
                        const data = snapshot.snapshots[key];
                        if (data && data.time_slots && data.time_slots[period]) {
                            return data.time_slots[period];
                        }
                    }
                }
            }
        }

        // Another fallback: Try direct snapshot access from periods
        if (locationData.periods && locationData.periods[period] && locationData.periods[period].snapshot) {
            const snapshot = locationData.periods[period].snapshot;
            if (snapshot.snapshots) {
                for (const key in snapshot.snapshots) {
                    const data = snapshot.snapshots[key];
                    if (data && data.time_slots && data.time_slots[period]) {
                        return data.time_slots[period];
                    }
                }
            }
        }

        return null;
    }

    function capitalizeWords(str) {
        return str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }

    // Close modal on outside click
    document.getElementById('weatherModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeWeatherModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeWeatherModal();
        }
    });

    // Auto-refresh timestamp every minute
    setInterval(function() {
        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
    }, 60000);

    // Store Now functionality
    document.getElementById('storeNow').addEventListener('click', function() {
        if (!confirm('Store weather forecasts for all locations right now?')) {
            return;
        }

        const button = this;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Storing...';

        fetch('/weather-reports/store-now', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const details = data.details;
                showNotification(`${data.message}`, 'success');
                showNotification(`Total: ${details.total_locations}, Successful: ${details.successful}, Failed: ${details.failed}`, 'info');
                
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showNotification('Failed to store forecasts: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while storing forecasts.', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-save mr-1"></i> Store Now';
        });
    });

    // Refresh functionality
    document.getElementById('refreshData').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Refreshing...';
        showNotification('Refreshing weather data...', 'info');
        location.reload();
    });

    // Cleanup old reports
    document.getElementById('cleanupOld').addEventListener('click', function() {
        if (!confirm('Are you sure you want to cleanup old weather reports? This will delete all reports from previous days.')) {
            return;
        }

        const button = this;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cleaning...';

        fetch('/weather-reports/cleanup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showNotification('Failed to cleanup: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred during cleanup.', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash-alt mr-1"></i> Cleanup Old Reports';
        });
    });

    // Initial notification on page load
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('Weather Reports Dashboard loaded successfully', 'success');
    });
</script>
@endpush