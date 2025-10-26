<!DOCTYPE html>
<html lang="en"
    x-data="{
        darkMode: localStorage.getItem('theme')
            ? localStorage.getItem('theme') === 'dark'
            : window.matchMedia('(prefers-color-scheme: dark)').matches
    }"
    x-init="
        $watch('darkMode', val => {
            localStorage.setItem('theme', val ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', val);
        });
        document.documentElement.classList.toggle('dark', darkMode);
    "
    :class="{ 'dark': darkMode }"
>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-data="{ 
        sidebarOpen: true,
        isMobile: window.innerWidth < 1024
    }"
    x-init="
        // Watch for window resize
        window.addEventListener('resize', () => {
            isMobile = window.innerWidth < 1024;
            if (isMobile) {
                sidebarOpen = false;
            }
        });
        
        // Close sidebar on mobile by default
        if (isMobile) {
            sidebarOpen = false;
        }
    "
    class="flex h-screen bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 overflow-hidden">

    <?php $user = Auth::user(); ?>

    <!-- Backdrop overlay for mobile -->
    <div x-show="sidebarOpen && isMobile" 
         x-cloak
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0  z-40 lg:hidden">
    </div>

    <!-- Sidebar -->
    <aside 
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        @click.away="if (isMobile) sidebarOpen = false"
        class="fixed lg:relative inset-y-0 left-0 z-50 w-60 bg-gray-800 text-white dark:bg-white dark:text-gray-800 shadow-md flex flex-col">

        <div class="p-4 font-bold text-white dark:text-gray-800 text-lg">
            <span>BukCast</span>
        </div>

        <nav class="flex-1 px-2 space-y-2 overflow-y-auto">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}"
                   @click="if (isMobile) sidebarOpen = false"
                    class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('admin.dashboard') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8" />
                    </svg>
                    <span>Dashboard</span>
                </a>
            @else
                <a href="{{ route('user.dashboard') }}"
                    @click="if (isMobile) sidebarOpen = false"
                    class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('user.dashboard') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8" />
                    </svg>
                    <span>Dashboard</span>
                </a>
            @endif

            @if(auth()->user()->role === 'admin')
                <a href="{{ route('map.show') }}"
                   @click="if (isMobile) sidebarOpen = false"
                   class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('map.show') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span>Maps</span>
                </a>
            @else
               <a href="{{ route('user.map.show') }}"
                  @click="if (isMobile) sidebarOpen = false"
               class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('user.map.show') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                <span>Maps</span>
            </a>
            @endif

            @if(auth()->user()->role === 'admin')
                <a href="{{ route('weather_reports.show') }}"
                   @click="if (isMobile) sidebarOpen = false"
                class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('weather_reports.show') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                </svg>
                <span>Weather Reports</span>
            </a>
            @else
                <a href="{{ route('user.weather_reports.show') }}"
                   @click="if (isMobile) sidebarOpen = false"
                class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('user.weather_reports.show') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                </svg>
                <span>Weather Reports</span>
            </a>
            @endif

            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.user_management') }}"
                   @click="if (isMobile) sidebarOpen = false"
                    class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('admin.user_management') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    <span>User Management</span>
                </a>

                <a href="{{ route('logs.show') }}"
                   @click="if (isMobile) sidebarOpen = false"
                    class="flex items-center space-x-2 px-4 py-2 dark:text-gray-800 hover:bg-white hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-white rounded {{ request()->routeIs('logs.show') ? 'bg-white text-gray-800 dark:bg-gray-800 dark:text-white' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Logs</span>
                </a>
            @endif
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 @yield('fullscreen', 'flex flex-col') w-full overflow-hidden transition-all duration-300">
        <!-- Header -->
        <header class="@yield('header-class', 'relative') bg-white text-gray-800 p-4 flex justify-between items-center shadow-sm dark:bg-gray-800 dark:text-white z-30">
            <div class="flex items-center space-x-3">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded hover:bg-gray-800 hover:text-white dark:hover:bg-white dark:hover:text-gray-800 focus:outline-none transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold truncate">@yield('header', 'Page')</h1>
            </div>

            <div x-data="{ profileOpen: false }" class="relative">
                <button @click="profileOpen = !profileOpen"
                    class="flex items-center space-x-2 focus:outline-none px-3 py-2 rounded transition-colors 
                           hover:bg-gray-700 hover:text-white">
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <div
                                class="w-8 h-8 bg-gray-800 text-white dark:bg-white dark:text-gray-800 rounded-full flex items-center justify-center font-semibold transition-colors duration-200">
                                {{ strtoupper(substr(Auth::user()->fname ?? Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>

                            @if(!Auth::user()->isCompleted)
                                <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white">
                                </div>
                            @endif
                        </div>
                        <span class="hidden md:block">{{ Auth::user()->fname ?? Auth::user()->name }}</span>
                        @if(!Auth::user()->isCompleted)
                            <span class="hidden lg:block text-xs bg-red-500 px-2 py-1 rounded-full">Incomplete</span>
                        @endif
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                        :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="profileOpen" 
                     x-cloak
                     @click.away="profileOpen = false" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100" 
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded-lg shadow-lg z-50 border border-gray-200">

                    <div class="px-4 py-3 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-900">
                            {{ Auth::user()->fname }} {{ Auth::user()->lname }}
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ Auth::user()->email }}
                        </p>
                        @if(!Auth::user()->isCompleted)
                            <p class="text-xs text-red-600 mt-1">
                                Profile incomplete
                            </p>
                        @endif
                    </div>

                    <div class="py-1">
                        @if(!Auth::user()->isCompleted)
                            <button onclick="openCompleteProfileModal(); document.querySelector('[x-data]').__x.$data.profileOpen = false"
                                class="flex items-center w-full px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                Complete Profile
                            </button>
                        @endif

                        <button onclick="openProfileModal(); document.querySelector('[x-data]').__x.$data.profileOpen = false"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            View Profile
                        </button>

                        <button onclick="openEditProfileModal(); document.querySelector('[x-data]').__x.$data.profileOpen = false"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Profile
                        </button>

                        <div class="border-t border-gray-200 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="@yield('main-class', 'flex-1 overflow-y-auto p-4 dark:bg-gray-200 ')">
            @yield('content')
        </main>
    </div>

    <!-- Rest of modals remain the same... -->
    @if(!Auth::user()->isCompleted)
        <div id="completeProfileModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="absolute inset-0"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
                <!-- Modal content remains the same -->
            </div>
        </div>
    @endif

    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        // Modal functions remain the same
        function openProfileModal() {
            const modal = document.getElementById('profileModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeProfileModal() {
            const modal = document.getElementById('profileModal');
            if (modal) modal.classList.add('hidden');
        }

        function openEditProfileModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeEditProfileModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) modal.classList.add('hidden');
        }

        function openCompleteProfileModal() {
            const modal = document.getElementById('completeProfileModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeCompleteProfileModal() {
            const modal = document.getElementById('completeProfileModal');
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener("DOMContentLoaded", function () {
            @if(session('showProfileModal'))
                openEditProfileModal();
            @elseif(!Auth::user()->isCompleted)
                setTimeout(function () {
                    openCompleteProfileModal();
                }, 1000);
            @endif
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeProfileModal();
                closeEditProfileModal();
                @if(Auth::user()->isCompleted)
                    closeCompleteProfileModal();
                @endif
            }
        });

        @if(!Auth::user()->isCompleted)
            document.addEventListener('click', function (event) {
                const modal = document.getElementById('completeProfileModal');
                if (modal && event.target === modal) {
                    event.preventDefault();
                }
            });
        @endif
    </script>

    @stack('scripts')
</body>

</html>