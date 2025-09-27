@extends('layouts.app')

@section('title', 'Weather Reports')
@section('header', 'Weather Reports')

@section('content')
<div class="space-y-6">
    <!-- Notification Container -->
    <div id="notificationContainer" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm"></div>

    <div class="flex justify-between mt-5">
         <h2 class="text-3xl font-bold text-gray-900 ml-2 ">Province of Bukidnon</h2>

        <button id="refreshData"
            class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-600">
            <i class="fas fa-sync-alt mr-1"></i> Refresh
        </button>
    </div>

    @if(isset($snapshots) && !$snapshots->isEmpty())
        <div class="bg-white rounded-lg shadow-sm p-6">
            
            <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-3" id="weatherCardsContainer">
                @foreach($snapshots as $snapshot)
                    @php
                        $location = $snapshot->weatherReport->location ?? null;
                        if (!$location) continue;
                        
                        $summary = $snapshot->getSummary();
                        $periods = $snapshot->getAvailableTimePeriods();
                    @endphp
                    
                    <div class="weather-card bg-white rounded-lg shadow-sm border-gray-100 hover:shadow-lg transition-all duration-200 cursor-pointer transform hover:scale-105" 
                         onclick="openWeatherModal('{{ $location->locID }}')"
                         data-location-name="{{ strtolower($location->name) }}">
                        <!-- Location Header -->
                        <div class="p-4 border-b border-gray-200  bg-gradient-to-r from-gray-50 to-blue-50 rounded-t-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-lg">
                                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                        {{ $location->name }}
                                    </h3>
                                   
                                </div>
                      
                            </div>
                        </div>

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
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="text-sm font-medium text-gray-700 mb-2">
                                        Available Periods
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
                                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-gray-700 text-xs font-medium">
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
<div id="weatherModal" class="fixed inset-0 backdrop-blur hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gray-800 text-white p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold" id="modalLocationName">Location Name</h2>
                 
                </div>
                <button onclick="closeWeatherModal()" class="text-white cursor-pointer hover:bg-opacity-20 rounded-full p-2 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-4 overflow-y-auto bg-white" style="max-height: calc(80vh - 100px);">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="modalWeatherContent">
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
            'info': 'bg-blue-600 border-blue-500 text-white',
            'success': 'bg-green-600 border-green-500 text-white',
            'warning': 'bg-yellow-600 border-yellow-500 text-white',
            'error': 'bg-red-600 border-red-500 text-white'
        };
        
        notification.className = `p-4 rounded-lg border-l-4 shadow-lg transform transition-all duration-300 ${typeClasses[type]}`;
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
    const locationSearch = document.getElementById('locationSearch');
    if (locationSearch) {
        locationSearch.addEventListener('input', function() {
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
    }

    function openWeatherModal(locID) {
        const locationData = weatherData[locID];
        if (!locationData) {
            showNotification('No weather data available for this location', 'error');
            return;
        }

        // Update modal header
        document.getElementById('modalLocationName').textContent = locationData.location.name;
        
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
                border: 'border-orange-200', 
                text: 'text-gray-800',
                accent: 'bg-orange-500',
                time: '6-10 AM'
            },
            'noon': { 
                icon: '☀️', 
                bg: 'bg-yellow-50', 
                border: 'border-yellow-200', 
                text: 'text-gray-800',
                accent: 'bg-yellow-500',
                time: '11 AM - 2 PM'
            },
            'afternoon': { 
                icon: '🌤️', 
                bg: 'bg-blue-50', 
                border: 'border-blue-200', 
                text: 'text-gray-800',
                accent: 'bg-blue-500',
                time: '3-5 PM'
            },
            'evening': { 
                icon: '🌆', 
                bg: 'bg-purple-50', 
                border: 'border-purple-200', 
                text: 'text-gray-800',
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
                <div class="border-2 rounded-lg ${config.border} ${config.bg} overflow-hidden shadow-sm">
                    <!-- Period Header -->
                    <div class="${config.accent} text-white p-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">${config.icon}</span>
                            <div>
                                <h3 class="font-semibold text-sm uppercase">${period}</h3>
                                <p class="text-xs opacity-90">${config.time}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Weather Content -->
                    <div class="p-4 bg-white ${config.text}">`;

            if (periodData) {
                const stormStatus = periodData.storm_status || 'clear';
                const statusClass = statusColors[stormStatus] || 'bg-gray-100 text-gray-800 border-gray-300';

                    html += `
                        <!-- Temperature Display -->
                        <div class="text-center mb-4">
                            <div class="text-3xl font-bold text-gray-900 mb-1">
                                ${parseFloat(periodData.temperature || 0).toFixed(1)}°C
                            </div>
                            <div class="text-xs text-gray-600 mb-2">
                                Feels like ${parseFloat(periodData.feels_like || periodData.temperature || 0).toFixed(1)}°C
                            </div>
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-medium ${statusClass} border">
                                ${capitalizeWords(stormStatus.replace(/_/g, ' '))}
                            </span>
                        </div>

                        <!-- Weather Description -->
                        <div class="text-center mb-4 pb-3 border-b border-gray-200">
                            <div class="flex items-center justify-center space-x-2">
                                ${periodData.weather_icon ? `<img src="https://openweathermap.org/img/wn/${periodData.weather_icon}@2x.png" alt="weather" class="w-12 h-12">` : ''}
                                <span class="text-sm font-medium text-gray-700">
                                    ${capitalizeWords(periodData.weather_desc || 'N/A')}
                                </span>
                            </div>
                        </div>

                        <!-- Precipitation Section -->
                        <div class="bg-gray-50 rounded-md p-3 mb-3 border border-gray-200">
                            <h4 class="font-medium text-gray-800 mb-2 flex items-center text-sm">
                                <i class="fas fa-cloud-rain text-blue-500 mr-1"></i>
                                Precipitation
                            </h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-xs">
                                        <i class="fas fa-percentage text-blue-400 mr-1"></i>Chance
                                    </span>
                                    <span class="font-semibold text-blue-600 text-sm">
                                        ${parseFloat(periodData.rain_chance || 0).toFixed(0)}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-xs">
                                        <i class="fas fa-tint text-blue-400 mr-1"></i>Amount
                                    </span>
                                    <span class="font-semibold text-blue-600 text-sm">
                                        ${parseFloat(periodData.rain_amount || 0).toFixed(2)} mm
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Metrics Grid -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-gray-50 rounded-md p-2 border border-gray-200">
                                <div class="text-xs text-gray-600 mb-1">
                                    <i class="fas fa-droplet mr-1"></i>Humidity
                                </div>
                                <div class="text-sm font-semibold text-gray-800">
                                    ${periodData.humidity || 0}%
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-md p-2 border border-gray-200">
                                <div class="text-xs text-gray-600 mb-1">
                                    <i class="fas fa-wind mr-1"></i>Wind
                                </div>
                                <div class="text-sm font-semibold text-gray-800">
                                    ${parseFloat(periodData.wind_speed || 0).toFixed(1)} m/s
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-md p-2 border border-gray-200">
                                <div class="text-xs text-gray-600 mb-1">
                                    <i class="fas fa-gauge mr-1"></i>Pressure
                                </div>
                                <div class="text-sm font-semibold text-gray-800">
                                    ${periodData.pressure || 0} hPa
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-md p-2 border border-gray-200">
                                <div class="text-xs text-gray-600 mb-1">
                                    <i class="fas fa-cloud mr-1"></i>Clouds
                                </div>
                                <div class="text-sm font-semibold text-gray-800">
                                    ${periodData.cloudiness || 0}%
                                </div>
                            </div>
                        </div>

                        <!-- Forecast Time -->
                        <div class="mt-3 text-center text-xs text-gray-500">
                            <i class="fas fa-clock mr-1"></i>
                            ${periodData.forecast_time || 'N/A'}
                        </div>
                    `;
                } else {
                    html += `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-cloud text-3xl mb-2"></i>
                            <p class="text-sm">No data available</p>
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
    const lastUpdatedElement = document.getElementById('lastUpdated');
    if (lastUpdatedElement) {
        setInterval(function() {
            lastUpdatedElement.textContent = new Date().toLocaleTimeString();
        }, 60000);
    }

    // Store Now functionality
     const storeNowButton = document.getElementById('storeNow');
    if (storeNowButton) {
        storeNowButton.addEventListener('click', function() {
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
    }



 // Replace the existing refresh functionality in your blade file with this:

document.getElementById('refreshData').addEventListener('click', function() {
    const button = this;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Refreshing...';
    showNotification('Starting data refresh...', 'info');

    fetch('/weather-reports/refresh-all', {
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
            
            // if (data.details) {
            //     showNotification(
            //         `Deleted: ${data.details.deleted_reports} reports, ${data.details.deleted_snapshots} snapshots`, 
            //         'info'
            //     );
            //     showNotification(
            //         `Stored: ${data.details.successful} locations successfully`, 
            //         'success'
            //     );
            // }
            
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showNotification('Refresh failed: ' + data.message, 'error');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Refresh';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred during refresh.', 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Refresh';
    });
});

    // Cleanup old reports
    const cleanupButton = document.getElementById('cleanupOld');
    if (cleanupButton) {
        cleanupButton.addEventListener('click', function() {
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
    }

  
</script>
@endpush