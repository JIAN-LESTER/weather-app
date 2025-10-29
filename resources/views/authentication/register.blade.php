<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 flex items-center justify-center min-h-screen">

    <main class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 sm:p-8">

        <h1 class="text-2xl font-bold text-center mb-6">BukCast Registration</h1>


        <form action="{{ route('register') }}" method="POST" class="space-y-5">
                @csrf
 
            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 
                              bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="you@example.com">
                       @error('email')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
            </div>


            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 
                              bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="••••••••">
                     @error('password')
    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
@enderror
            </div>

     
            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 
                              bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" 
                       placeholder="••••••••">
                     @error('password_confirmation')
    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
@enderror
            </div>


            <button type="submit" 
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition">
                Register
            </button>
        </form>

        <div class="flex items-center my-6">
            <div class="flex-grow h-px bg-gray-300 dark:bg-gray-700"></div>
            <span class="px-3 text-sm text-gray-500">OR</span>
            <div class="flex-grow h-px bg-gray-300 dark:bg-gray-700"></div>
        </div>

     <!-- Google Sign In Button - Prominent Position -->
        <a href="{{ route('auth.google') }}" 
           class="w-full flex items-center justify-center gap-3 border border-gray-300 dark:border-gray-600 
                  bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium py-3 px-4 rounded-md 
                  hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200 shadow-sm hover:shadow-md mb-6">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5">
            Sign up with Google
        </a>
        <p class="text-center text-sm mt-6">
            Already have an account? 
            <a href="{{ route('loginForm') }}" class="text-blue-600 hover:underline dark:text-blue-400">Login</a>
        </p>
    </main>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    @if(session('success'))
        Toastify({
            text: "{{ session('success') }}",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "linear-gradient(to right, #10b981, #059669)",
            stopOnFocus: true,
        }).showToast();
    @endif

    @if(session('error'))
        Toastify({
            text: "{{ session('error') }}",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "linear-gradient(to right, #ef4444, #dc2626)",
            stopOnFocus: true,
        }).showToast();
    @endif

    @if($errors->any())
        Toastify({
            text: "{{ $errors->first() }}",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "linear-gradient(to right, #ef4444, #dc2626)",
            stopOnFocus: true,
        }).showToast();
    @endif
    </script>


   <script>
const form = document.querySelector('form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('password_confirmation');

form.addEventListener('submit', function(e) {
    let isValid = true;

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailInput.value || !emailRegex.test(emailInput.value)) {
        showError(emailInput, 'Please enter a valid email address');
        isValid = false;
    } else {
        clearError(emailInput);
    }


    // Password validation
    if (!passwordInput.value || passwordInput.value.length < 8) {
        showError(passwordInput, 'Password must be at least 8 characters long');
        isValid = false;
    } else {
        clearError(passwordInput);
    }

    // Confirm password validation
    if (confirmPasswordInput.value !== passwordInput.value) {
        showError(confirmPasswordInput, 'Passwords do not match');
        isValid = false;
    } else {
        clearError(confirmPasswordInput);
    }

    if (!isValid) e.preventDefault();
});

// Helper functions
function showError(input, message) {
    input.classList.add('border-red-500');
    input.classList.remove('border-gray-300');

    let errorSpan = input.parentNode.querySelector('.error-message');
    if (!errorSpan) {
        errorSpan = document.createElement('span');
        errorSpan.className = 'error-message text-red-500 text-xs mt-1 block';
        input.parentNode.appendChild(errorSpan);
    }
    errorSpan.textContent = message;
}

function clearError(input) {
    input.classList.remove('border-red-500');
    input.classList.add('border-gray-300');

    const errorSpan = input.parentNode.querySelector('.error-message');
    if (errorSpan) errorSpan.remove();
}

// Real-time validation
emailInput.addEventListener('blur', () => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (emailInput.value && !emailRegex.test(emailInput.value)) {
        showError(emailInput, 'Please enter a valid email address');
    } else {
        clearError(emailInput);
    }
});

passwordInput.addEventListener('blur', () => {
    if (passwordInput.value && passwordInput.value.length < 8) {
        showError(passwordInput, 'Password must be at least 8 characters long');
    } else {
        clearError(passwordInput);
    }
});

confirmPasswordInput.addEventListener('blur', () => {
    if (confirmPasswordInput.value && confirmPasswordInput.value !== passwordInput.value) {
        showError(confirmPasswordInput, 'Passwords do not match');
    } else {
        clearError(confirmPasswordInput);
    }
});
</script>


</body>
</html>
