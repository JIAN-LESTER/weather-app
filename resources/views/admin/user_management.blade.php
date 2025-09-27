@extends('layouts.app')

@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
    <header class="p-4 flex justify-end items-center">

        <button onclick="openModal('addUserModal')"
            class="flex items-center px-3 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add User
        </button>

    </header>
    <main class="bg-white shadow-md rounded-lg overflow-hidden">



    
        <section class="p-4 border-t border-b border-gray-200" aria-label="Search users">
            <form method="GET" action="{{ route('admin.user_management') }}" class="flex gap-2 items-center" role="search">
                <label for="search" class="sr-only">Search by name</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name..."
                    class="border-gray-200 border rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-gray-500">
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.user_management') }}" class="text-gray-500 hover:underline px-2">Clear</a>
                @endif
            </form>
        </section>

   
        <section class="overflow-x-auto" aria-labelledby="users-table">
            <table class="min-w-full border border-gray-200 text-sm" role="table">
                <caption id="users-table" class="sr-only">List of all users</caption>
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">Name</th>
                        <th scope="col" class="px-6 py-3 text-left">Role</th>
                        <th scope="col" class="px-6 py-3 text-left">Status</th>
                        <th scope="col" class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $user->fname }} {{ $user->lname }}</p>
                                <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs rounded-full 
                                                    {{ $user->role === 'Admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs rounded-full 
                                                    {{ $user->user_status === 'active' ? 'bg-gray-100 text-gray-700' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $user->user_status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-4 py-2 text-center">
                                <div class="flex items-center justify-center space-x-3 text-center">
                         
                                  <button onclick="editUser('{{ $user->userID }}')" class="text-blue-500 hover:text-blue-700" title="Edit">

                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536M9 11l6.586-6.586a2 2 0 112.828 2.828L11.828 13.828a2 2 0 01-1.414.586H9v-2a2 2 0 01.586-1.414z" />
                                        </svg>Edit
                                    </button>

                                
                                    <button onclick="openDeleteModal('{{ route('admin.users-destroy', $user->userID) }}')"
                                        class="text-red-500 hover:text-red-700 text-center" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3" />
                                        </svg>Delete
                                    </button>
                                </div>
                            </td>


                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

@if($users->total() > 0)
<div class="flex items-center justify-between p-4 text-sm text-gray-600">

    <div>
        Showing 
        <span class="font-medium">{{ $users->firstItem() }}</span>
        to 
        <span class="font-medium">{{ $users->lastItem() }}</span>
        of 
        <span class="font-medium">{{ $users->total() }}</span> users
    </div>


    <div class="flex space-x-2">
        @if($users->onFirstPage())
            <span class="px-3 py-1 rounded-lg bg-gray-200 text-gray-500 cursor-not-allowed">Previous</span>
        @else
            <a href="{{ $users->previousPageUrl() }}"
                class="px-3 py-1 rounded-lg bg-gray-600 text-white hover:bg-gray-700">Previous</a>
        @endif

        @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}"
                class="px-3 py-1 rounded-lg bg-gray-600 text-white hover:bg-gray-700">Next</a>
        @else
            <span class="px-3 py-1 rounded-lg bg-gray-200 text-gray-500 cursor-not-allowed">Next</span>
        @endif
    </div>
</div>
@endif

        </section>
    </main>




    <div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm hidden">

        <div class="absolute inset-0 " onclick="closeModal('addUserModal')"></div>

     
        <div class="relative bg-gray-100 dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden">
            <header class="bg-gray-600 text-white p-5 rounded-t-2xl">
                <h2 class="text-xl font-semibold">Add New User</h2>
            </header>

            <form action="{{ route('admin.users-store') }}" method="POST" class="p-6 md:p-8 space-y-6 relative z-10">
                @csrf

        
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First
                            Name</label>
                        <input type="text" name="fname" id="fname" required
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label for="lname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last
                            Name</label>
                        <input type="text" name="lname" id="lname" required
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                </div>

             
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="email" required
                        class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" name="password" id="password" required
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label for="password_confirmation"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                </div>

        
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                    <select name="role" id="role"
                        class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

            
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeModal('addUserModal')"
                        class="px-6 py-2 rounded-lg bg-gray-400 text-white hover:bg-gray-500 w-full sm:w-auto">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 w-full sm:w-auto">Add
                        User</button>
                </div>
            </form>
        </div>
    </div>


    <div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm hidden">
   
        <div class="absolute inset-0 " onclick="closeModal('editUserModal')"></div>

     
        <div class="relative bg-gray-100 dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden">
            <header class="bg-gray-600 text-white p-5 rounded-t-2xl">
                <h2 class="text-xl font-semibold">Edit User</h2>
            </header>

            <form id="editUserForm" method="POST" class="p-6 md:p-8 space-y-6 relative z-10">
                @csrf
                @method('PUT')
                <input type="hidden" id="editUserId">

        
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_fname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First
                            Name</label>
                        <input type="text" name="fname" id="edit_fname" required
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label for="edit_lname" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last
                            Name</label>
                        <input type="text" name="lname" id="edit_lname" required
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                </div>

          
                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" name="email" id="edit_email" required
                        class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                </div>

      
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New
                            Password (Optional)</label>
                        <input type="password" name="password" id="edit_password"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                    </div>
                    <div>
                        <label for="edit_role"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                        <select name="role" id="edit_role"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

            
                <div>
                    <label for="edit_status"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="user_status" id="edit_status"
                        class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 px-4 py-2 focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

           
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeModal('editUserModal')"
                        class="px-6 py-2 rounded-lg bg-gray-400 text-white hover:bg-gray-500 w-full sm:w-auto">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 rounded-lg bg-gray-600 text-white hover:bg-gray-700 w-full sm:w-auto">Update
                        User</button>
                </div>
            </form>
        </div>
    </div>



    <div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-md mx-4 overflow-hidden">

  
            <div class="bg-gray-600 text-white px-4 py-3 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Confirm Delete</h2>
                <button type="button" onclick="closeModal('deleteConfirmModal')"
                    class="text-white hover:text-gray-200">✕</button>
            </div>

 
            <div class="p-6">
                <p class="text-gray-700 dark:text-gray-200">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>
            </div>


            <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeModal('deleteConfirmModal')"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Cancel
                </button>

                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-400">
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
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
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

    </script>

@endsection