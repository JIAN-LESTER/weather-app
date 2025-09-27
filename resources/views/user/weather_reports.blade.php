@extends('layouts.app')

@section('title', 'Weather Reports Management')
@section('header', 'Weather Reports Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Stats -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg text-white p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Weather Reports Dashboard</h1>
                <p class="text-blue-100 mt-1">Live updates every 15 minutes</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ count($todaySnapshots ?? []) }}</div>
                <div class="text-sm text-blue-100">Active Locations Today</div>
            </div>
        </div>
    </div>



    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex gap-3">
                <select id="locationFilter" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Locations</option>
                    @if(isset($snapshots) && $snapshots->count() > 0)
                        @foreach($snapshots->unique('weatherReport.location.locID') as $snapshot)
                            @if($snapshot->weatherReport && $snapshot->weatherReport->location)
                                <option value="{{ $snapshot->weatherReport->location->locID }}">
                                    {{ $snapshot->weatherReport->location->name }}
                                </option>
                            @endif
                        @endforeach
                    @endif
                </select>
                
                <select id="dateFilter" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="week">This Week</option>
                </select>
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
            
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($snapshots as $snapshot)
                    @php
                        $location = $snapshot->weatherReport->location ?? null;
                        if (!$location) continue;
                        
                        $summary = $snapshot->getSummary();
                        $periods = $snapshot->getAvailableTimePeriods();
                    @endphp
                    
                    <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <!-- Location Header -->
                        <div class="p-4 border-b bg-gray-50 rounded-t-lg">
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

    function openWeatherModal(locID) {
        const locationData = weatherData[locID];
        if (!locationData) return;

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
            const snapshot = locationData.periods[period];
            
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

            if (snapshot) {
                // Get period data
                const periodData = getPeriodData(snapshot, period);
                
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

    function getPeriodData(snapshot, period) {
        // Try different data structures
        if (snapshot.snapshots) {
            // Check direct snapshots property
            for (const key in snapshot.snapshots) {
                const data = snapshot.snapshots[key];
                if (data && data.time_slots && data.time_slots[period]) {
                    return data.time_slots[period];
                }
            }
        }
        
        // Check if snapshot has weather_report with snapshots
        if (snapshot.weather_report && snapshot.weather_report.snapshots) {
            for (const snap of snapshot.weather_report.snapshots) {
                if (snap.snapshots) {
                    for (const key in snap.snapshots) {
                        const data = snap.snapshots[key];
                        if (data && data.time_slots && data.time_slots[period]) {
                            return data.time_slots[period];
                        }
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

    // Auto-refresh timestamp every minute (since data updates every 15 mins)
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
                let message = `${data.message}\n\n`;
                message += `Total locations: ${details.total_locations}\n`;
                message += `Successful: ${details.successful}\n`;
                message += `Failed: ${details.failed}`;
                
                if (details.errors && details.errors.length > 0) {
                    message += `\n\nErrors:\n${details.errors.join('\n')}`;
                }
                
                alert(message);
                location.reload();
            } else {
                alert('Failed to store forecasts: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while storing forecasts.');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-save mr-1"></i> Store Now';
        });
    });

    // Refresh functionality
    document.getElementById('refreshData').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Refreshing...';
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
                alert(data.message);
                location.reload();
            } else {
                alert('Failed to cleanup: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during cleanup.');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash-alt mr-1"></i> Cleanup Old Reports';
        });
    });
</script>
@endpush