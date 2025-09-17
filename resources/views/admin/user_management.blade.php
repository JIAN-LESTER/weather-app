@extends('layouts.app')

@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-4 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-700">Users</h2>
        <a href="{{ route('admin.users.create') }}" 
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add User
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Role</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50">
                    <!-- Name + Email -->
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                        <div class="text-gray-500 text-sm">{{ $user->email }}</div>
                    </td>

                    <!-- Role -->
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $user->role === 'Admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $user->isActive ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $user->isActive ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 text-center space-x-2">
                        <a href="{{ route('admin.users.edit', $user->id) }}" 
                           class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" 
                              method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to delete this user?')"
                                    class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach

                @if($users->isEmpty())
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                        No users found.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
