<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: true }" class="flex h-screen bg-gray-100">

   <!-- Sidebar -->
<aside 
    class="bg-white shadow-md flex flex-col transition-all duration-300"
    :class="sidebarOpen ? 'w-64' : 'w-16'">

    <div class="p-4 font-bold text-green-700 text-lg truncate">
        <span x-show="sidebarOpen" class="transition-opacity">My App</span>
        <span x-show="!sidebarOpen" class="transition-opacity">M</span>
    </div>

    <nav class="flex-1 px-2 space-y-2">

        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-200 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                       d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m-4 0h8" />
            </svg>
            <span x-show="sidebarOpen" class="transition-opacity">Dashboard</span>
        </a>

        <!-- Maps -->
        <a href="#" 
           class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-200 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                       d="M12 20l9-7-9-7-9 7 9 7z" />
            </svg>
            <span x-show="sidebarOpen" class="transition-opacity">Maps</span>
        </a>

        <!-- Weather -->
        <a href="#" 
           class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-200 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                       d="M3 15a4 4 0 018 0h7a4 4 0 010 8H5a4 4 0 01-2-7.5" />
            </svg>
            <span x-show="sidebarOpen" class="transition-opacity">Weather</span>
        </a>

        <!-- Snapshots -->
        <a href="#" 
           class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-200 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                       d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12a4 4 0 100-8 4 4 0 000 8z" />
            </svg>
            <span x-show="sidebarOpen" class="transition-opacity">Snapshots</span>
        </a>

        <!-- User Management -->
        <a href="#" 
           class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-200 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                       d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87M12 12a4 4 0 100-8 4 4 0 000 8z" />
            </svg>
            <span x-show="sidebarOpen" class="transition-opacity">User Management</span>
        </a>

        <!-- Logs -->
        <a href="#" 
           class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-200 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                       d="M9 17v-6h13v6M9 5v4h13V5M3 9h.01M3 13h.01M3 17h.01" />
            </svg>
            <span x-show="sidebarOpen" class="transition-opacity">Logs</span>
        </a>

    </nav>
</aside>


    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Appbar -->
        <header class="bg-green-600 text-white p-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <!-- Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded hover:bg-green-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                               d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold">@yield('header', 'Page')</h1>
            </div>
            <div>User Menu</div>
        </header>

        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
