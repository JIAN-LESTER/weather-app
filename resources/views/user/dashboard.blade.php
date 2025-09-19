@extends('layouts.app')

@section('title', 'User Dashboard')
@section('header', 'User Dashboard')

@section('content')
<main class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">User Dashboard</h1>
    <p class="mt-2 text-gray-600 dark:text-gray-400">Welcome back, {{ auth()->user()->fname }}!</p>
    
   
    @if(session('success'))
        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    

    @if($errors->any())
        <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    

    
@endsection

@push('scripts')
<script>
       
        function openProfileModal() {
            const modal = document.getElementById('profileModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeProfileModal() {
            const modal = document.getElementById('profileModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openEditProfileModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeEditProfileModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openCompleteProfileModal() {
            const modal = document.getElementById('completeProfileModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeCompleteProfileModal() {
            const modal = document.getElementById('completeProfileModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            @if(session('showProfileModal'))
                openEditProfileModal();
            @elseif(!Auth::user()->isCompleted)
              
                setTimeout(function() {
                    openCompleteProfileModal();
                }, 1000);
            @endif
        });

      
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeProfileModal();
                closeEditProfileModal();
                @if(Auth::user()->isCompleted)
                    closeCompleteProfileModal();
                @endif
            }
        });

   
        @if(!Auth::user()->isCompleted)
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('completeProfileModal');
            if (modal && event.target === modal) {
               
                event.preventDefault();
            }
        });
        @endif
    </script>
@endpush