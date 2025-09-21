@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    <div class="max-w-8xl mx-auto">

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4 space-y-3 lg:space-y-0">
            <div class="flex-1">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-800 mb-1">
                    Welcome, {{ auth()->user()->fname ?? 'User' }}
                </h1>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-3 w-full lg:w-auto">
                <div class="relative flex-1 lg:flex-initial">

                </div>

                <button
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
             
                </button>
            </div>
        </div>

        <div id="weatherDashboard">
       
            <div id="loadingState" class="text-center py-12">
                <div
                    class="inline-block animate-spin rounded-full h-8 w-8 sm:h-10 sm:w-10 border-4 border-gray-300 dark:border-gray-600 border-t-blue-600 dark:border-t-blue-400 mb-3">
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Loading weather data...</p>
            </div>

         
            <div id="weatherContent" class="hidden space-y-3 sm:space-y-4">

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-3 sm:gap-4">
      
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">

           
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 pb-3 border-b border-gray-200 dark:border-gray-600">
                            <div class="mb-2 sm:mb-0">
                                <div class="flex items-center text-gray-700 dark:text-gray-300 text-xs sm:text-sm mb-1">
                                    <i class="fas fa-map-marker-alt mr-1.5 text-blue-500 dark:text-blue-400 text-xs"></i>
                                    <span id="location" class="font-medium">Loading location...</span>
                                </div>
                                <div class="text-gray-600 dark:text-gray-400 text-xs">
                                    <span id="currentDay" class="font-medium"></span>
                                    <span class="mx-1.5 hidden sm:inline">•</span>
                                    <span id="currentDate"></span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1.5 text-red-800 dark:text-gray-400 text-sm">
                                <i class="fas fa-temperature-high text-red-500 text-xs"></i>
                                <i class="fas fa-tint text-blue-400 text-xs"></i>
                            </div>
                        </div>

          
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0 mb-4">
                            <div class="flex items-center space-x-3 sm:space-x-4">
                                <div class="text-3xl sm:text-4xl lg:text-5xl" id="mainWeatherIcon">
                                    <i class="fas fa-cloud-rain text-blue-500 dark:text-blue-400"></i>
                                </div>
                                <div>
                                    <div class="text-3xl sm:text-4xl lg:text-5xl font-light text-gray-900 dark:text-white"
                                        id="mainTemp">25°</div>
                                    <div class="text-gray-700 dark:text-gray-300 text-sm sm:text-base" id="feelsLike">
                                        Feels like 28°</div>
                                    <div class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm mt-1"
                                        id="tempRange">H: 30° L: 20°</div>
                                </div>
                            </div>

                            <div class="text-left sm:text-right w-full sm:w-auto">
                                <p id="weatherDescription"
                                    class="text-base sm:text-lg text-gray-800 dark:text-gray-200 capitalize mb-1">Heavy Rain</p>
                                <div class="text-gray-600 dark:text-gray-400 text-xs" id="current-date-display"></div>
                            </div>
                        </div>

           
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-gray-700 dark:text-gray-300 text-xs font-medium">Rain Chance</span>
                                    <i class="fas fa-tint text-blue-500 dark:text-blue-400 text-sm"></i>
                                </div>
                                <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                                    <span id="rainChance">90</span><span class="text-sm">%</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-gray-700 dark:text-gray-300 text-xs font-medium">Rainfall</span>
                                    <i class="fas fa-cloud-rain text-blue-500 dark:text-blue-400 text-sm"></i>
                                </div>
                                <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                                    <span id="rainfall">2.5</span> <span class="text-xs font-normal">mm/h</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-gray-700 dark:text-gray-300 text-xs font-medium">Humidity</span>
                                    <i class="fas fa-water text-blue-500 dark:text-blue-400 text-sm"></i>
                                </div>
                                <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                                    <span id="humidity">85</span><span class="text-xs">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

              
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">User Statistics</h3>
                            <i class="fas fa-users text-blue-500 dark:text-blue-400 text-base sm:text-lg"></i>
                        </div>
                        
                        <div class="space-y-4">
                    
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-gray-700 dark:text-gray-300 text-sm font-medium">Total Users</span>
                                    <i class="fas fa-user-friends text-green-500 dark:text-green-400 text-sm"></i>
                                </div>
                                <div class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ $totalUsers ?? 0 }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Registered users</div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                            
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-gray-700 dark:text-gray-300 text-xs font-medium">Active</span>
                                        <i class="fas fa-user-check text-blue-500 dark:text-blue-400 text-xs"></i>
                                    </div>
                                    <div class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">
                                        {{ $activeUsers ?? 0 }}
                                    </div>
                                </div>

                           
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-gray-700 dark:text-gray-300 text-xs font-medium">Verified</span>
                                        <i class="fas fa-user-shield text-purple-500 dark:text-purple-400 text-xs"></i>
                                    </div>
                                    <div class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">
                                        {{ $verifiedUsers ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200 dark:border-gray-700 shadow-lg">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-2 sm:mb-0">Recent Logs</h3>
                        <a href="{{ route('logs.show') }}" 
                           class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors text-sm flex items-center space-x-1">
                            <span>View All</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                  <div class="space-y-3">
    @if(isset($recentLogs) && $recentLogs->count() > 0)
        @foreach($recentLogs as $log)
            <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                <div class="flex items-start space-x-3 flex-1">
                    
              
                    <div class="flex-shrink-0 mt-1">
                        @if($log->fname === 'error')
                            <i class="fas fa-exclamation-circle text-red-500 text-sm"></i>
                        @elseif($log->fname === 'warning')
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-sm"></i>
                        @elseif($log->fname === 'info')
                            <i class="fas fa-info-circle text-blue-500 text-sm"></i>
                        @else
                            <i class="fas fa-circle text-gray-500 text-sm"></i>
                        @endif
                    </div>

               
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-white font-medium truncate">
                            {{ $log->action ?? 'No message' }}
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span class="capitalize">{{ $log->fname ?? 'info' }}</span>
                            <span>{{ $log->created_at ? $log->created_at->format('M d, H:i') : 'Unknown time' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-8">
            <i class="fas fa-clipboard-list text-gray-400 text-3xl mb-3"></i>
            <p class="text-gray-500 dark:text-gray-400">No recent logs available</p>
        </div>
    @endif
</div>
                </div>
            </div>

            <!-- Error State -->
            <div id="errorState" class="hidden text-center py-12">
                <div class="text-red-500 dark:text-red-400 text-3xl sm:text-5xl mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-gray-900 dark:text-white text-base sm:text-lg mb-2">
                    Unable to Load Weather Data
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4 text-sm">
                    Please check your internet connection and try again.
                </p>
                <button onclick="location.reload()"
                    class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors shadow-lg text-sm">
                    Try Again
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const weatherDashboard = document.getElementById("weatherDashboard");
            const loadingState = document.getElementById("loadingState");
            const weatherContent = document.getElementById("weatherContent");
            const errorState = document.getElementById("errorState");

            // OpenWeatherMap API configuration
            const API_KEY = "{{ env('OPENWEATHER_API_KEY') }}";
            const BASE_URL = "https://api.openweathermap.org/data/2.5";

            const weatherIcons = {
                "01d": "fas fa-sun text-yellow-500 dark:text-yellow-400",
                "01n": "fas fa-moon text-blue-400 dark:text-blue-300",
                "02d": "fas fa-cloud-sun text-yellow-500 dark:text-yellow-400",
                "02n": "fas fa-cloud-moon text-blue-400 dark:text-blue-300",
                "03d": "fas fa-cloud text-gray-500 dark:text-gray-400",
                "03n": "fas fa-cloud text-gray-500 dark:text-gray-400",
                "04d": "fas fa-cloud text-gray-600 dark:text-gray-400",
                "04n": "fas fa-cloud text-gray-600 dark:text-gray-400",
                "09d": "fas fa-cloud-showers-heavy text-blue-500 dark:text-blue-400",
                "09n": "fas fa-cloud-showers-heavy text-blue-500 dark:text-blue-400",
                "10d": "fas fa-cloud-rain text-blue-500 dark:text-blue-400",
                "10n": "fas fa-cloud-rain text-blue-500 dark:text-blue-400",
                "11d": "fas fa-bolt text-yellow-600 dark:text-yellow-500",
                "11n": "fas fa-bolt text-yellow-600 dark:text-yellow-500",
                "13d": "fas fa-snowflake text-blue-200 dark:text-blue-300",
                "13n": "fas fa-snowflake text-blue-200 dark:text-blue-300",
                "50d": "fas fa-smog text-gray-500 dark:text-gray-400",
                "50n": "fas fa-smog text-gray-500 dark:text-gray-400",
            };

            // Set current date and time
            const now = new Date();
            const currentDateOptions = { weekday: "long", day: "2-digit", month: "short", year: "numeric" };
            document.getElementById("current-date-display").textContent = now.toLocaleDateString("en-US", currentDateOptions);
            document.getElementById("currentDay").textContent = now.toLocaleDateString("en-US", { weekday: "long" });
            document.getElementById("currentDate").textContent = now.toLocaleDateString("en-US", { day: "2-digit", month: "short", year: "numeric" });

            function getWeatherIcon(iconCode) {
                return weatherIcons[iconCode] || "fas fa-question text-gray-500 dark:text-gray-400";
            }

            function displayCurrentWeather(weather) {
                const temp = Math.round(weather.main.temp);
                const feelsLike = Math.round(weather.main.feels_like);
                const tempMin = Math.round(weather.main.temp_min);
                const tempMax = Math.round(weather.main.temp_max);
                const humidity = weather.main.humidity;
                const description = weather.weather[0]?.description ?? "No data";
                const icon = weather.weather[0]?.icon ?? "01d";
                const rainAmount = weather.rain?.["1h"] || 0;
                const locationName = weather.name ?? "Unknown";
                const country = weather.sys?.country ?? "";

                // Update location
                document.getElementById("location").textContent = `${locationName}, ${country}`;

                // Update main weather info
                document.getElementById("mainTemp").textContent = `${temp}°`;
                document.getElementById("feelsLike").textContent = `Feels like ${feelsLike}°`;
                document.getElementById("tempRange").textContent = `H: ${tempMax}° L: ${tempMin}°`;
                document.getElementById("weatherDescription").textContent = description.charAt(0).toUpperCase() + description.slice(1);
                document.getElementById("mainWeatherIcon").innerHTML = `<i class="${getWeatherIcon(icon)}"></i>`;

                // Update precipitation details
                const rainChance = rainAmount > 0 ? 100 : humidity > 80 ? Math.round(humidity * 0.8) : Math.round(humidity * 0.3);
                document.getElementById("rainChance").textContent = rainChance;
                document.getElementById("rainfall").textContent = rainAmount.toFixed(1);
                document.getElementById("humidity").textContent = humidity;
            }

            async function fetchWeatherData(lat, lon) {
                try {
                    const currentResponse = await fetch(
                        `${BASE_URL}/weather?lat=${lat}&lon=${lon}&appid=${API_KEY}&units=metric`
                    );
                    const currentWeather = await currentResponse.json();

                    displayCurrentWeather(currentWeather);

                    // Show content and hide loading
                    loadingState.classList.add("hidden");
                    weatherContent.classList.remove("hidden");
                } catch (error) {
                    console.error("Error fetching weather data:", error);
                    showError();
                }
            }

            function showError() {
                loadingState.classList.add("hidden");
                weatherContent.classList.add("hidden");
                errorState.classList.remove("hidden");
            }

            // Initialize geolocation and weather fetching
            if (!navigator.geolocation) {
                showError();
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    fetchWeatherData(lat, lng);
                },
                (error) => {
                    console.error("Geolocation error:", error);
                    // Fallback to General Santos City
                    fetchWeatherData(6.1164, 125.1716);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000, // 5 minutes
                }
            );
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Enhanced backdrop blur */
        .backdrop-blur-xl {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* Smooth transitions */
        * {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Dark mode scrollbar */
        @media (prefers-color-scheme: dark) {
            ::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.05);
            }

            ::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.2);
            }

            ::-webkit-scrollbar-thumb:hover {
                background: rgba(255, 255, 255, 0.3);
            }
        }

        /* Loading spinner animation */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* Weather card animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .weather-card {
            animation: slideInUp 0.6s ease-out forwards;
        }

        .weather-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .weather-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .weather-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .weather-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        /* Focus styles */
        input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Glass effect enhancements */
        .bg-white\/90 {
            background: rgba(255, 255, 255, 0.9);
        }

        .bg-white\/80 {
            background: rgba(255, 255, 255, 0.8);
        }

        .bg-white\/70 {
            background: rgba(255, 255, 255, 0.7);
        }

        @media (prefers-color-scheme: dark) {
            .dark\:bg-gray-800\/90 {
                background: rgba(31, 41, 55, 0.9);
            }

            .dark\:bg-gray-800\/80 {
                background: rgba(31, 41, 55, 0.8);
            }

            .dark\:bg-gray-800\/70 {
                background: rgba(31, 41, 55, 0.7);
            }

            .dark\:bg-gray-700\/50 {
                background: rgba(55, 65, 81, 0.5);
            }
        }

        /* Responsive text scaling */
        @media (max-width: 640px) {
            .text-responsive-xl {
                font-size: 1.5rem;
                line-height: 2rem;
            }
        }

        /* Enhanced hover effects */
        .hover\:shadow-2xl:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Weather icon hover effects */
        .weather-icon-hover:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease-in-out;
        }
    </style>
@endpush