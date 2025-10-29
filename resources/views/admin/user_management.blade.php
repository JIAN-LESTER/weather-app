@extends('layouts.app')

@section('title', 'User Management')
@section('header', 'User Management')

 
@section('content')
    <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-right sm:items-center gap-4">
    

            <!-- Add User Button -->
            <button onclick="openModal('addUserModal')"
                class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 sm:px-6 py-3 rounded-xl bg-gradient-to-r from-gray-700 to-gray-800 text-white hover:from-gray-800 hover:to-gray-900 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="font-semibold">Add New User</span>
            </button>
        </div>
    </div>
    <!-- Main Content Card -->
    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
        <!-- Search Section with Enhanced Design -->
        <div class="p-4 sm:p-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('admin.user_management') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                        class="pl-10 w-full border-gray-300 dark:border-gray-600 border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 shadow-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-initial bg-gradient-to-r from-gray-600 to-gray-700 text-white px-6 py-3 rounded-xl hover:from-gray-700 hover:to-gray-800 shadow-md hover:shadow-lg transition-all duration-300 font-semibold whitespace-nowrap">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.user_management') }}" class="flex-1 sm:flex-initial text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 px-4 py-3 text-center font-medium transition-colors rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700">Clear</a>
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
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-800 dark:bg-white dark:text-gray-800 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                        {{ strtoupper(substr($user->fname, 0, 1)) }}{{ strtoupper(substr($user->lname, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->fname }} {{ $user->lname }}</p>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900/40 dark:to-red-800/40 dark:text-red-300' : 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 dark:from-blue-900/40 dark:to-blue-800/40 dark:text-blue-300' }} shadow-sm">
                                    @if($user->role === 'admin')
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                    @endif
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full {{ $user->user_status === 'active' ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900/40 dark:to-green-800/40 dark:text-green-300' : 'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 dark:from-gray-700 dark:to-gray-600 dark:text-gray-300' }} shadow-sm">
                                    <span class="w-2 h-2 rounded-full {{ $user->user_status === 'active' ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                    {{ $user->user_status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editUser('{{ $user->userID }}')" 
                                        class="p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-all duration-200 group-hover:scale-110" 
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button onclick="confirmDelete('{{ route('admin.users-destroy', $user->userID) }}')"
                                        class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-all duration-200 group-hover:scale-110" 
                                        title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-lg font-medium">No users found</p>
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
            @forelse($users as $user)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <div class="space-y-3">
                        <!-- User Info with Avatar -->
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl dark:bg-white dark:text-gray-800 flex items-center justify-center text-white font-bold shadow-lg flex-shrink-0">
                                {{ strtoupper(substr($user->fname, 0, 1)) }}{{ strtoupper(substr($user->lname, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $user->fname }} {{ $user->lname }}</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm truncate">{{ $user->email }}</p>
                            </div>
                        </div>

                        <!-- Role and Status -->
                        <div class="flex gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900/40 dark:to-red-800/40 dark:text-red-300' : 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 dark:from-blue-900/40 dark:to-blue-800/40 dark:text-blue-300' }} shadow-sm">
                                {{ ucfirst($user->role) }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full {{ $user->user_status === 'active' ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900/40 dark:to-green-800/40 dark:text-green-300' : 'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 dark:from-gray-700 dark:to-gray-600 dark:text-gray-300' }} shadow-sm">
                                <span class="w-2 h-2 rounded-full {{ $user->user_status === 'active' ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                {{ $user->user_status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2">
                            <button onclick="editUser('{{ $user->userID }}')" 
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 text-blue-700 dark:text-blue-400 rounded-xl hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-900/30 dark:hover:to-blue-800/30 transition-all duration-200 shadow-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </button>
                            <button onclick="confirmDelete('{{ route('admin.users-destroy', $user->userID) }}')"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 text-red-700 dark:text-red-400 rounded-xl hover:from-red-100 hover:to-red-200 dark:hover:from-red-900/30 dark:hover:to-red-800/30 transition-all duration-200 shadow-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-lg font-medium">No users found</p>
                        <p class="text-sm mt-1">Try adjusting your search criteria</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Enhanced Pagination -->
        @if($users->total() > 0)
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 sm:p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 gap-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing 
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $users->firstItem() }}</span>
                    to 
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $users->lastItem() }}</span>
                    of 
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $users->total() }}</span> users
                </div>

                <div class="flex gap-2">
                    @if($users->onFirstPage())
                        <span class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed font-medium">Previous</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}"
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-gray-600 to-gray-700 text-white hover:from-gray-700 hover:to-gray-800 shadow-md hover:shadow-lg transition-all duration-200 font-medium">Previous</a>
                    @endif

                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}"
                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-gray-600 to-gray-700 text-white hover:from-gray-700 hover:to-gray-800 shadow-md hover:shadow-lg transition-all duration-200 font-medium">Next</a>
                    @else
                        <span class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed font-medium">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>


    <div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-md hidden p-4">
        <div class="absolute inset-0" onclick="closeModal('addUserModal')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] overflow-y-auto transform transition-all">
            <header class="bg-gradient-to-r from-gray-700 to-gray-900 text-white p-6 sticky top-0 z-10">
                <h2 class="text-2xl font-bold">Add New User</h2>
                <p class="text-sm text-gray-300 mt-1">Fill in the details below</p>
            </header>

            <form action="{{ route('admin.users-store') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="fname" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                        <input type="text" name="fname" id="fname" 
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                       @error('fname')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    <div>
                        <label for="lname" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                        <input type="text" name="lname" id="lname" 
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                       @error('lname')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" 
                        class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                   @error('email')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                    </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <input type="password" name="password" id="password" 
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                       @error('password')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                       @error('password_confirmation')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                </div>

                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Role</label>
                    <select name="role" id="role"
                        class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t-2 border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeModal('addUserModal')"
                        class="px-6 py-3 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-all duration-200 w-full sm:w-auto">Cancel</button>
                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-gray-700 to-gray-900 text-white hover:from-gray-800 hover:to-black shadow-lg hover:shadow-xl transition-all duration-200 font-semibold w-full sm:w-auto">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-md hidden p-4">
        <div class="absolute inset-0" onclick="closeModal('editUserModal')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] overflow-y-auto transform transition-all">
            <header class="bg-gradient-to-r from-gray-700 to-gray-900 text-white p-6 sticky top-0 z-10">
                <h2 class="text-2xl font-bold">Edit User</h2>
                <p class="text-sm text-gray-300 mt-1">Update user information</p>
            </header>

            <form id="editUserForm" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" id="editUserId">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_fname" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                        <input type="text" name="fname" id="edit_fname" 
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                       @error('edit_fname')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    <div>
                        <label for="edit_lname" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                        <input type="text" name="lname" id="edit_lname" 
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                       @error('edit_lname')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                </div>

                <div>
                    <label for="edit_email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="edit_email" 
                        class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                  @error('edit_email')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                    </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">New Password <span class="text-gray-400 text-xs">(Optional)</span></label>
                        <input type="password" name="password" id="edit_password"
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                      @error('edit_password')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    <div>
                        <label for="edit_role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <select name="role" id="edit_role"
                            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="edit_status" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="user_status" id="edit_status"
                        class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-transparent transition-all">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t-2 border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeModal('editUserModal')"
                        class="px-6 py-3 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-all duration-200 w-full sm:w-auto">Cancel</button>
                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 font-semibold w-full sm:w-auto">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-md p-4">
        
    </div>

    <script>
        function openDeleteModal(actionUrl) {
            document.getElementById('deleteForm').action = actionUrl;
            openModal('deleteConfirmModal');
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

       function editUser(userId) {
    fetch(`/admin/user_crud/edit/${userId}`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to fetch user data');
            return res.json();
        })
        .then(user => {
            document.getElementById('editUserId').value = user.userID;
            document.getElementById('edit_fname').value = user.fname;
            document.getElementById('edit_lname').value = user.lname;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.user_status;
            document.getElementById('editUserForm').action = `/admin/user_crud/update/${user.id}`;
            openModal('editUserModal');
        })
        .catch(error => {
            Toastify({
                text: "Failed to load user data",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "linear-gradient(to right, #ef4444, #dc2626)",
            }).showToast();
        });
}

        function confirmDelete(actionUrl, userName) {
    Swal.fire({
        title: 'Are you sure?',
        html: `You are about to delete <strong>${userName}</strong>.<br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete user!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true,
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

  
        // Prevent body scroll when modal is open
        window.addEventListener('load', function() {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            if (modal.classList.contains('hidden')) {
                                document.body.style.overflow = 'auto';
                            } else {
                                document.body.style.overflow = 'hidden';
                            }
                        }
                    });
                });
                observer.observe(modal, { attributes: true });
            });
        });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const addForm = document.querySelector('#addUserModal form');
    const editForm = document.getElementById('editUserForm');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Show inline error
    function showError(input, message) {
        input.classList.add('border-red-500');
        input.classList.remove('border-gray-200', 'dark:border-gray-600');

        let errorSpan = input.nextElementSibling;
        if (!errorSpan || !errorSpan.classList.contains('error-message')) {
            errorSpan = document.createElement('span');
            errorSpan.className = 'error-message text-red-500 text-xs mt-1 block';
            input.parentNode.appendChild(errorSpan);
        }
        errorSpan.textContent = message;
    }

    // Clear inline error
    function clearError(input) {
        input.classList.remove('border-red-500');
        input.classList.add('border-gray-200', 'dark:border-gray-600');
        const errorSpan = input.nextElementSibling;
        if (errorSpan && errorSpan.classList.contains('error-message')) {
            errorSpan.remove();
        }
    }

    // Validate individual form
    function validateForm(form, isEdit = false) {
        let isValid = true;
        const fname = form.querySelector('[name="fname"]');
        const lname = form.querySelector('[name="lname"]');
        const email = form.querySelector('[name="email"]');
        const password = form.querySelector('[name="password"]');
        const confirmPassword = form.querySelector('[name="password_confirmation"]');

        // First name
        if (!fname.value.trim()) {
            showError(fname, 'First name is required');
            isValid = false;
        } else clearError(fname);

        // Last name
        if (!lname.value.trim()) {
            showError(lname, 'Last name is required');
            isValid = false;
        } else clearError(lname);

        // Email
        if (!email.value.trim()) {
            showError(email, 'Email is required');
            isValid = false;
        } else if (!emailRegex.test(email.value)) {
            showError(email, 'Invalid email format');
            isValid = false;
        } else clearError(email);

    
        if (!isEdit) {
            if (!password.value.trim()) {
                showError(password, 'Password is required');
                isValid = false;
            } else if (password.value.length < 8) {
                showError(password, 'Password must be at least 8 characters');
                isValid = false;
            } else clearError(password);

            // Confirm password
            if (confirmPassword && password.value !== confirmPassword.value) {
                showError(confirmPassword, 'Passwords do not match');
                isValid = false;
            } else if (confirmPassword) {
                clearError(confirmPassword);
            }
        } else {
            // Edit form: password optional but must be valid if filled
            if (password.value) {
                if (password.value.length < 8) {
                    showError(password, 'Password must be at least 8 characters');
                    isValid = false;
                } else clearError(password);
            } else clearError(password);
        }

        return isValid;
    }

    // Attach event listeners
    if (addForm) {
        addForm.addEventListener('submit', (e) => {
            if (!validateForm(addForm, false)) e.preventDefault();
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', (e) => {
            if (!validateForm(editForm, true)) e.preventDefault();
        });
    }
});
</script>

@endsection