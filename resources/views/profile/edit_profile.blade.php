<!-- Edit Profile Modal -->
<div id="editProfileModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 dark:bg-opacity-70">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-lg mx-4 overflow-hidden">
    
    <!-- Header -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Edit Profile</h2>
      <button onclick="closeEditProfileModal()" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">
        ✕
      </button>
    </div>

    <!-- Body -->
    <form id="editProfileForm" action="{{ route('profile.update') }}" method="POST" class="p-6 space-y-4">
      @csrf
      @method('PUT')

      <div>
        <label for="fname" class="block text-sm font-medium text-gray-700 dark:text-gray-200">First Name</label>
        <input type="text" name="fname" id="fname" value="{{ auth()->user()->fname }}"
               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-green-500 focus:border-green-500">
      </div>

      <div>
        <label for="lname" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Last Name</label>
        <input type="text" name="lname" id="lname" value="{{ auth()->user()->lname }}"
               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-green-500 focus:border-green-500">
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
        <input type="email" name="email" id="email" value="{{ auth()->user()->email }}"
               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-green-500 focus:border-green-500">
      </div>

      <div>
        <label for="old_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Current Password</label>
        <input type="password" name="old_password" id="old_password"
               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-green-500 focus:border-green-500">
      </div>

      <div>
        <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">New Password</label>
        <input type="password" name="new_password" id="new_password"
               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-green-500 focus:border-green-500">
      </div>

      <div>
        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirm New Password</label>
        <input type="password" name="new_password_confirmation" id="new_password_confirmation"
               class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-green-500 focus:border-green-500">
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeEditProfileModal()"
                class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
          Cancel
        </button>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>
