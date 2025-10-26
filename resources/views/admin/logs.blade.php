@extends('layouts.app')

@section('title', 'Logs')
@section('header', 'Logs')

@section('content')
<main class="bg-white shadow-md rounded-lg overflow-hidden dark:text-gray-800 text-base">

    <!-- Search Section -->
<section class="p-4 lg:p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 rounded-t-lg shadow-sm" aria-label="Search logs">
    <form method="GET" action="{{ route('logs.show') }}" class="space-y-3" role="search">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 lg:gap-4 items-end">

            <!-- Search Input -->
            <div class="sm:col-span-2 lg:col-span-3">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Search logs..."
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-green-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm">
            </div>

            <!-- Filter Dropdown -->
            <div class="lg:col-span-1">
                <label for="filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter By</label>
                <select name="filter" id="filter"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-green-600 focus:border-green-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm">
                    <option value="action" {{ ($filter ?? 'action') === 'action' ? 'selected' : '' }}>Action</option>
                    <option value="user" {{ ($filter ?? '') === 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="flex gap-2 lg:col-span-2">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-green-600 focus:border-green-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>

                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-green-600 focus:border-green-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 lg:justify-end">
                <button type="submit"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-500 hover:text-white transition-colors duration-200 text-sm font-medium shadow-sm w-full sm:w-auto">
                    Search
                </button>
                @if(request()->query())
                    <a href="{{ route('logs.show') }}"
                        class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-100 transition-colors duration-200 text-center text-sm font-medium shadow-sm w-full sm:w-auto">
                        Clear
                    </a>
                @endif
            </div>

        </div>
    </form>
</section>


    <!-- Table Section - Desktop -->
    <section class="hidden md:block overflow-x-auto" aria-labelledby="logs-table">
        <table class="min-w-full border-collapse text-sm">
            <caption id="logs-table" class="sr-only">List of system logs</caption>
            <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left font-semibold">User</th>
                    <th scope="col" class="px-6 py-3 text-left font-semibold">Action</th>
                    <th scope="col" class="px-6 py-3 text-left font-semibold">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $log->user->fname ?? 'Unknown' }} {{ $log->user->lname ?? '' }}</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $log->user->email ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $log->action }}</td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $log->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="font-medium">No logs found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <!-- Card View - Mobile -->
    <section class="md:hidden divide-y dark:bg-gray-800 bg-white text-gray-800 dark:text-white divide-gray-200 dark:divide-gray-700" aria-label="Logs list">
        @forelse($logs as $log)
            <div class="p-4  dark:hover:bg-gray-700 transition-colors">
                <!-- User Info -->
                <div class="mb-3">
                    <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                        {{ $log->user->fname ?? 'Unknown' }} {{ $log->user->lname ?? '' }}
                    </p>
                    @if($log->user && $log->user->email)
                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">{{ $log->user->email }}</p>
                    @endif
                </div>

                <!-- Action & Date -->
                <div class="space-y-2">
                    <div class="flex items-start">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 w-16 flex-shrink-0">Action:</span>
                        <span class="text-sm text-gray-700 dark:text-gray-300 flex-1">{{ $log->action }}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 w-16 flex-shrink-0">Date:</span>
                        <span class="text-sm text-gray-700 dark:text-gray-300 flex-1">{{ $log->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No logs found</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try adjusting your filters</p>
            </div>
        @endforelse
    </section>

    <!-- Pagination -->
    @if($logs->total() > 0)
        <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700 gap-3 bg-gray-50 dark:bg-gray-800">
            
            <!-- Results Count -->
            <div class="text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left">
                Showing 
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $logs->firstItem() }}</span>
                to 
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $logs->lastItem() }}</span>
                of 
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $logs->total() }}</span> logs
            </div>

            <!-- Pagination Buttons -->
            <div class="flex gap-2">
                @if($logs->onFirstPage())
                    <span class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed text-sm font-medium">
                        <span class="hidden sm:inline">Previous</span>
                        <span class="sm:hidden">Prev</span>
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}"
                        class="px-4 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 transition-colors shadow-sm text-sm font-medium">
                        <span class="hidden sm:inline">Previous</span>
                        <span class="sm:hidden">Prev</span>
                    </a>
                @endif

                <!-- Current Page Indicator (Mobile) -->
                <span class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium sm:hidden">
                    {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                </span>

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}"
                        class="px-4 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 transition-colors shadow-sm text-sm font-medium">
                        Next
                    </a>
                @else
                    <span class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed text-sm font-medium">
                        Next
                    </span>
                @endif
            </div>
        </div>
    @endif

</main>
@endsection