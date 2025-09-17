<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 flex items-center justify-center min-h-screen">

    <main class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 sm:p-8">
        <!-- Title -->
        <h1 class="text-2xl font-bold text-center mb-6">Create an Account</h1>

        <!-- Form -->
        <form action="{{ route('register') }}" method="POST" class="space-y-5">
                @csrf
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 
                              bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="you@example.com">
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 
                              bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="••••••••">
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 
                              bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="••••••••">
            </div>

            <!-- Register Button -->
            <button type="submit" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition">
                Register
            </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center my-6">
            <div class="flex-grow h-px bg-gray-300 dark:bg-gray-700"></div>
            <span class="px-3 text-sm text-gray-500">OR</span>
            <div class="flex-grow h-px bg-gray-300 dark:bg-gray-700"></div>
        </div>

        <!-- Google Sign Up -->
        <button type="button" 
                class="w-full flex items-center justify-center gap-2 border border-gray-300 dark:border-gray-600 
                       bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5">
            Sign up with Google
        </button>

        <!-- Already have an account -->
        <p class="text-center text-sm mt-6">
            Already have an account? 
            <a href="{{ route('loginForm') }}" class="text-blue-600 hover:underline dark:text-blue-400">Login</a>
        </p>
    </main>

</body>
</html>
