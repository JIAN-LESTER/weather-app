@extends('layouts.app')

@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
    <header class="p-4 flex justify-end items-center">
        <button onclick="openModal('addUserModal')"
            class="flex items-center px-3 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden sm:inline">Add User</span>
            <span class="sm:hidden">Add</span>
        </button>
    </header>

    <main class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <!-- Search Section -->
        <section class="p-4 border-t border-b border-gray-200 dark:border-gray-700" aria-label="Search users">
            <form method="GET" action="{{ route('admin.user_management') }}" class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center" role="search">
                <label for="search" class="sr-only">Search by name</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name..."
                    class="border-gray-300 dark:border-gray-600 border rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-initial bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 whitespace-nowrap">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.user_management') }}" class="flex-1 sm:flex-initial text-gray-500 dark:text-gray-400 hover:underline px-2 py-2 text-center">Clear</a>
                    @endif
                </div>
            </form>
        </section>

        <!-- Desktop Table View (hidden on mobile) -->
        <section class="hidden md:block overflow-x-auto" aria-labelledby="users-table">
            <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm" role="table">
                <caption id="users-table" class="sr-only">List of all users</caption>
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">Name</th>
                        <th scope="col" class="px-6 py-3 text-left">Role</th>
                        <th scope="col" class="px-6 py-3 text-left">Status</th>
                        <th scope="col" class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $user->fname }} {{ $user->lname }}</p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $user->user_status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $user->user_status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <button onclick="editUser('{{ $user->userID }}')" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center gap-1" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536M9 11l6.586-6.586a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-1.414.586H9v-2a2 2 0 01.586-1.414z" />
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button onclick="openDeleteModal('{{ route('admin.users-destroy', $user->userID) }}')"
                                        class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 flex items-center gap-1" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3" />
                                        </svg>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <!-- Mobile Card View (visible on mobile only) -->
        <section class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($users as $user)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <div class="space-y-3">
                        <!-- User Info -->
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $user->fname }} {{ $user->lname }}</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $user->email }}</p>
                        </div>

                        <!-- Role and Status -->
                        <div class="flex gap-2 flex-wrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ $user->role === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full {{ $user->user_status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $user->user_status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2">
                            <button onclick="editUser('{{ $user->userID }}')" 
                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536M9 11l6.586-6.586a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-1.414.586H9v-2a2 2 0 01.586-1.414z" />
                                </svg>
                                Edit
                            </button>
                            <button onclick="openDeleteModal('{{ route('admin.users-destroy', $user->userID) }}')"
                                class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    No users found.
                </div>
            @endforelse
        </section>

        <!-- Pagination -->
        @if($users->total() > 0)
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 text-sm text-gray-600 dark:text-gray-400 gap-3 border-t border-gray-200 dark:border-gray-700">
                <!-- Results Count -->
                <div class="text-center sm:text-left">
                    Showing 
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $users->firstItem() }}</span>
                    to 
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $users->lastItem() }}</span>
                    of 
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $users->total() }}</span> users
                </div>

                <!-- Pagination Buttons -->
                <div class="flex space-x-2">
                    @if($users->onFirstPage())
                        <span class="px-3 py-1 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-500 cursor-not-allowed">Previous</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}"
                            class="px-3 py-1 rounded-lg bg-gray-600 dark:bg-gray-700 text-white hover:bg-gray-700 dark:hover:bg-gray-600">Previous</a>
                    @endif

                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}"
                            class="px-3 py-1 rounded-lg bg-gray-600 dark:bg-gray-700 text-white hover:bg-gray-700 dark:hover:bg-gray-600">Next</a>
                    @else
                        <span class="px-3 py-1 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-500 cursor-not-allowed">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </main>

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 backdrop-blur-sm hidden p-4">
        <div class="absolute inset-0" onclick="closeModal('addUserModal')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <header class="bg-gray-600 dark:bg-gray-900 text-white p-5 rounded-t-2xl sticky top-0 z-10">
                <h2 class="text-xl font-semibold">Add New User</h2>
            </header>

            <form action="{{ route('admin.users-store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="fname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                        <input type="text" name="fname" id="fname" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                    </div>
                    <div>
                        <label for="lname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                        <input type="text" name="lname" id="lname" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="email" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                    </div>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select name="role" id="role"
                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeModal('addUserModal')"
                        class="px-6 py-2 rounded-lg bg-gray-400 dark:bg-gray-600 text-white hover:bg-gray-500 dark:hover:bg-gray-500 w-full sm:w-auto">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 rounded-lg bg-gray-600 dark:bg-gray-700 text-white hover:bg-gray-700 dark:hover:bg-gray-600 w-full sm:w-auto">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 backdrop-blur-sm hidden p-4">
        <div class="absolute inset-0" onclick="closeModal('editUserModal')"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <header class="bg-gray-600 dark:bg-gray-900 text-white p-5 rounded-t-2xl sticky top-0 z-10">
                <h2 class="text-xl font-semibold">Edit User</h2>
            </header>

            <form id="editUserForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="editUserId">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_fname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                        <input type="text" name="fname" id="edit_fname" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                    </div>
                    <div>
                        <label for="edit_lname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                        <input type="text" name="lname" id="edit_lname" required
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                    </div>
                </div>

                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="edit_email" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password (Optional)</label>
                        <input type="password" name="password" id="edit_password"
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                    </div>
                    <div>
                        <label for="edit_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                        <select name="role" id="edit_role"
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="edit_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="user_status" id="edit_status"
                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:border-gray-500 dark:focus:border-gray-400">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeModal('editUserModal')"
                        class="px-6 py-2 rounded-lg bg-gray-400 dark:bg-gray-600 text-white hover:bg-gray-500 dark:hover:bg-gray-500 w-full sm:w-auto">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 rounded-lg bg-gray-600 dark:bg-gray-700 text-white hover:bg-gray-700 dark:hover:bg-gray-600 w-full sm:w-auto">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-md overflow-hidden">
            <div class="bg-gray-600 dark:bg-gray-900 text-white px-4 py-3 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Confirm Delete</h2>
                <button type="button" onclick="closeModal('deleteConfirmModal')"
                    class="text-white hover:text-gray-200">✕</button>
            </div>

            <div class="p-6">
                <p class="text-gray-700 dark:text-gray-200">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeModal('deleteConfirmModal')"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 w-full sm:w-auto">
                    Cancel
                </button>

                <form id="deleteForm" method="POST" action="" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full px-4 py-2 rounded-lg bg-red-600 dark:bg-red-700 text-white hover:bg-red-700 dark:hover:bg-red-600 focus:ring-2 focus:ring-red-400">
                        Delete
                    </button>
                </form>
            </div>
        </div>
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
                .then(res => res.json())
                .then(user => {
                    document.getElementById('editUserId').value = user.userID;
                    document.getElementById('edit_fname').value = user.fname;
                    document.getElementById('edit_lname').value = user.lname;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_status').value = user.user_status;
                    document.getElementById('editUserForm').action = `/admin/user_crud/update/${user.id}`;
                    openModal('editUserModal');
                });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modals = ['addUserModal', 'editUserModal', 'deleteConfirmModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    closeModal(modalId);
                }
            });
        });

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

@endsection