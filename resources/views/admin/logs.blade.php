@extends('layouts.app')

@section('title', 'Logs')
@section('header', 'Logs')

@section('content')
<main class="bg-white shadow-md rounded-lg overflow-hidden">

    <!-- Search & Filter Section -->
<section class="p-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 rounded-md shadow-sm mb-4" aria-label="Search logs">
    <form method="GET" action="{{ route('logs.show') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end" role="search">
        
        <!-- Search Input -->
        <div class="md:col-span-2">
            <label for="search" class="sr-only">Search logs</label>
            <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Search logs..."
                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
        </div>

        <!-- Filter Select -->
        <div>
            <label for="filter" class="sr-only">Filter by</label>
            <select name="filter" id="filter" 
                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                <option value="all" {{ ($filter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                <option value="user" {{ ($filter ?? '') === 'user' ? 'selected' : '' }}>User</option>
                <option value="action" {{ ($filter ?? '') === 'action' ? 'selected' : '' }}>Action</option>
            </select>
        </div>

        <!-- Start Date -->
        <div>
            <label for="start_date" class="sr-only">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
        </div>

        <!-- End Date -->
        <div>
            <label for="end_date" class="sr-only">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
        </div>

        <!-- Buttons -->
        <div class="flex gap-2 md:col-span-1">
            <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200">Search</button>
            @if(request()->query())
                <a href="{{ route('logs.show') }}" class="flex-1 px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition-colors duration-200 text-center">Clear</a>
            @endif
        </div>
    </form>
</section>


    <!-- Logs Table Section -->
    <section class="overflow-x-auto" aria-labelledby="logs-table">
        <table class="min-w-full border border-gray-200 text-sm">
            <caption id="logs-table" class="sr-only">List of system logs</caption>
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">User</th>
                    <th scope="col" class="px-6 py-3 text-left">Action</th>
                    <th scope="col" class="px-6 py-3 text-left">Date</th>
                </tr>
            </thead>
 <tbody class="divide-y divide-gray-200">
    @forelse($logs as $log)
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <p class="font-medium text-gray-900">{{ $log->user->fname ?? 'Unknown' }} {{ $log->user->lname ?? '' }}</p>
                <p class="text-gray-500 text-sm">{{ $log->user->email ?? '' }}</p>
            </td>
            <td class="px-6 py-4">{{ $log->action }}</td>
            <td class="px-6 py-4">{{ $log->created_at->format('M d, Y H:i') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                No logs found.
            </td>
        </tr>
    @endforelse
</tbody>
        </table>

   @if($logs->total() > 0)
<div class="flex items-center justify-between p-4 text-sm text-gray-600">
    <!-- Pagination Info -->
    <div>
        Showing 
        <span class="font-medium">{{ $logs->firstItem() }}</span>
        to 
        <span class="font-medium">{{ $logs->lastItem() }}</span>
        of 
        <span class="font-medium">{{ $logs->total() }}</span> logs
    </div>

    <!-- Previous / Next Buttons -->
    <div class="flex space-x-2">
        @if($logs->onFirstPage())
            <span class="px-3 py-1 rounded-lg bg-gray-200 text-gray-500 cursor-not-allowed">Previous</span>
        @else
            <a href="{{ $logs->previousPageUrl() }}"
                class="px-3 py-1 rounded-lg bg-green-600 text-white hover:bg-green-700">Previous</a>
        @endif

        @if($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}"
                class="px-3 py-1 rounded-lg bg-green-600 text-white hover:bg-green-700">Next</a>
        @else
            <span class="px-3 py-1 rounded-lg bg-gray-200 text-gray-500 cursor-not-allowed">Next</span>
        @endif
    </div>
</div>
@endif
    </section>

</main>
@endsection
