<!-- Profile Modal -->
<div id="profileModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 dark:bg-opacity-70">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full max-w-lg mx-4 overflow-hidden">
    
    <!-- Header -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Profile</h2>
      <button onclick="closeProfileModal()" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">
        ✕
      </button>
    </div>

    <!-- Body -->
    <div class="p-6 space-y-4">
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">First Name</p>
        <p class="text-base font-medium text-gray-800 dark:text-gray-100">{{ auth()->user()->fname }}</p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Last Name</p>
        <p class="text-base font-medium text-gray-800 dark:text-gray-100">{{ auth()->user()->lname }}</p>
      </div>
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
        <p class="text-base font-medium text-gray-800 dark:text-gray-100">{{ auth()->user()->email }}</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex justify-end p-4 border-t border-gray-200 dark:border-gray-700">
      <button onclick="closeProfileModal()"
              class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
        Close
      </button>
    </div>
  </div>
</div>
