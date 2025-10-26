@extends('layouts.app')

@section('title', 'Logs')
@section('header', 'Logs')

@section('content')
    <!-- Header with Stats -->
   

    <!-- Main Content Card -->
    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
        
        <!-- Enhanced Search Section -->
<div class="p-4 sm:p-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 border-b border-gray-200 dark:border-gray-700">
    <form method="GET" action="{{ route('logs.show') }}" class="space-y-4">
        
        <!-- Search and Filter Row - All in One Line -->
        <div class="flex flex-wrap lg:flex-nowrap items-end gap-3">
            
            <!-- Search Input with Icon -->
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Search by user or action..."
                        class="pl-10 w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 shadow-sm transition-all">
                </div>
            </div>

            <!-- Filter Dropdown -->
            <div class="w-full sm:w-auto sm:min-w-[150px]">
                <label for="filter" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Filter By</label>
                <select name="filter" id="filter"
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm transition-all">
                    <option value="action" {{ ($filter ?? 'action') === 'action' ? 'selected' : '' }}>Action</option>
                    <option value="user" {{ ($filter ?? '') === 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <!-- Start Date -->
            <div class="w-full sm:w-auto sm:min-w-[150px]">
                <label for="start_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm transition-all">
            </div>

            <!-- End Date -->
            <div class="w-full sm:w-auto sm:min-w-[150px]">
                <label for="end_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                    class="w-full border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm transition-all">
            </div>

            <!-- Search Button -->
            <div class="w-full sm:w-auto">
                <button type="submit"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white px-6 py-3 rounded-xl hover:from-gray-700 hover:to-gray-800 shadow-md hover:shadow-lg transition-all duration-300 font-semibold whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Search
                </button>
            </div>

            <!-- Clear Button -->
            @if(request()->query())
                <div class="w-full sm:w-auto">
                    <a href="{{ route('logs.show') }}"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-100 transition-all duration-300 font-semibold shadow-md whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                </div>
            @endif
        </div>
    </form>
</div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
        
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full dark:bg-white dark:text-gray-800 flex items-center justify-center text-white font-bold text-sm shadow-md flex-shrink-0">
                                        {{ strtoupper(substr($log->user->fname ?? 'U', 0, 1)) }}{{ strtoupper(substr($log->user->lname ?? 'N', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $log->user->fname ?? 'Unknown' }} {{ $log->user->lname ?? '' }}</p>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm truncate">{{ $log->user->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                            
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $log->action }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $log->created_at->format('M d, Y') }}</span>
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">{{ $log->created_at->format('H:i:s') }}</span>
                                </div>
                            </td>
      
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium">No logs found</p>
                                    <p class="text-sm mt-1">Try adjusting your search criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($logs as $log)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="space-y-3">
                        
                        <!-- User Info with Avatar -->
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl dark:bg-white dark:text-gray-800 flex items-center justify-center text-white font-bold shadow-lg flex-shrink-0">
                                {{ strtoupper(substr($log->user->fname ?? 'U', 0, 1)) }}{{ strtoupper(substr($log->user->lname ?? 'N', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $log->user->fname ?? 'Unknown' }} {{ $log->user->lname ?? '' }}</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm truncate">{{ $log->user->email ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">ACTION</span>
                            </div>
                            <div class="flex items-center gap-2">
                                
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $log->action }}</span>
                            </div>
                        </div>

                        <!-- Date and Status -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $log->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900/40 dark:to-green-800/40 dark:text-green-300 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                Done
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-medium">No logs found</p>
                        <p class="text-sm mt-1">Try adjusting your search criteria</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Enhanced Pagination -->
        @if($logs->total() > 0)
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 gap-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing 
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $logs->firstItem() }}</span>
                    to 
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $logs->lastItem() }}</span>
                    of 
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $logs->total() }}</span> logs
                </div>

                <div class="flex gap-2">
                    @if($logs->onFirstPage())
                        <span class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed font-medium">
                            <span class="hidden sm:inline">Previous</span>
                            <span class="sm:hidden">Prev</span>
                        </span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}"
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-gray-600 to-gray-700 text-white hover:from-gray-700 hover:to-gray-800 shadow-md hover:shadow-lg transition-all duration-200 font-medium">
                            <span class="hidden sm:inline">Previous</span>
                            <span class="sm:hidden">Prev</span>
                        </a>
                    @endif

                    <!-- Page indicator for mobile -->
                    <span class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium sm:hidden">
                        {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                    </span>

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}"
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-gray-600 to-gray-700 text-white hover:from-gray-700 hover:to-gray-800 shadow-md hover:shadow-lg transition-all duration-200 font-medium">
                            Next
                        </a>
                    @else
                        <span class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed font-medium">
                            Next
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        // Export logs to CSV
        function exportLogs() {
            // Get current filters
            const search = document.getElementById('search').value;
            const filter = document.getElementById('filter').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            // Build query string
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (filter) params.append('filter', filter);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            params.append('export', 'csv');

            // Create notification
            showNotification('Preparing export...', 'info');

            // Trigger download (you'll need to implement the backend route)
            window.location.href = `{{ route('logs.show') }}?${params.toString()}`;
            
            setTimeout(() => {
                showNotification('Export complete!', 'success');
            }, 1500);
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const colors = {
                info: 'from-blue-500 to-blue-600',
                success: 'from-green-500 to-green-600',
                error: 'from-red-500 to-red-600'
            };

            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-xl bg-gradient-to-r ${colors[type]} text-white shadow-xl transform transition-all duration-300 translate-x-0`;
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Auto-submit form on date change
        document.querySelectorAll('#start_date, #end_date').forEach(input => {
            input.addEventListener('change', function() {
                // Optional: auto-submit on date change
                // this.form.submit();
            });
        });
    </script>

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(107, 114, 128, 0.7);
        }

        /* Smooth transitions */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }

        /* Pulse animation for status indicator */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Loading animation */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
@endsection