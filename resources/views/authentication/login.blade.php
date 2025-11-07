<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
</head>


<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 flex items-center justify-center min-h-screen">

    <main class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 sm:p-8">

        <h1 class="text-2xl font-bold text-center mb-6">BukCast Login</h1>


        <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

           <div>
    <label for="email" class="block text-sm font-medium mb-1">Email</label>
    <input type="email" id="email" name="email" value="{{ old('email') }}" 
           class="w-full px-4 py-2 rounded-md border @error('email') border-red-500 @else border-gray-300 @enderror dark:border-gray-700 
                  bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-gray-500 outline-none" 
           placeholder="you@example.com">
    @error('email')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
</div>


            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input type="password" id="password" name="password" 
                       class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-700 
                              bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-gray-500 outline-none" 
                       placeholder="••••••••">
            </div>

            <div class="text-right">
                <a href="#" class="text-sm text-gray-600 hover:underline dark:text-gray-400">Forgot password?</a>
            </div>

      
            <button type="submit" 
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition">
                Login
            </button>
        </form>

        <div class="flex items-center my-6">
            <div class="flex-grow h-px bg-gray-300 dark:bg-gray-700"></div>
            <span class="px-3 text-sm text-gray-500">OR</span>
            <div class="flex-grow h-px bg-gray-300 dark:bg-gray-700"></div>
        </div>

    

        <a href="{{ route('auth.google') }}" 
           class="w-full flex items-center justify-center gap-3 border border-gray-300 dark:border-gray-600 
                  bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium py-3 px-4 rounded-md 
                  hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200 shadow-sm hover:shadow-md mb-6">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5">
            Log in with Google
        </a>

 
        <p class="text-center text-sm mt-6">
            Don’t have an account? 
            <a href="{{ route('registerForm') }}" class="text-gray-600 hover:underline dark:text-gray-400">Register</a>
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

  form.addEventListener('submit', function(e) {
      let isValid = true;

      // Email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailInput.value.trim()) {
          showError(emailInput, 'Email is required');
          isValid = false;
      } else if (!emailRegex.test(emailInput.value)) {
          showError(emailInput, 'Please enter a valid email address');
          isValid = false;
      } else {
          clearError(emailInput);
      }

      // Password validation
      if (!passwordInput.value.trim()) {
          showError(passwordInput, 'Password is required');
          isValid = false;
      } else if (passwordInput.value.length < 8) {
          showError(passwordInput, 'Password must be at least 8 characters');
          isValid = false;
      } else {
          clearError(passwordInput);
      }

      if (!isValid) {
          e.preventDefault();
      }
  });

  function showError(input, message) {
      input.classList.add('border-red-500');
      input.classList.remove('border-gray-300');
      
      let errorSpan = input.nextElementSibling;
      if (!errorSpan || !errorSpan.classList.contains('error-message')) {
          errorSpan = document.createElement('span');
          errorSpan.className = 'error-message text-red-500 text-xs mt-1 block';
          input.parentNode.appendChild(errorSpan);
      }
      errorSpan.textContent = message;
  }

  function clearError(input) {
      input.classList.remove('border-red-500');
      input.classList.add('border-gray-300');
      
      const errorSpan = input.nextElementSibling;
      if (errorSpan && errorSpan.classList.contains('error-message')) {
          errorSpan.remove();
      }
  }


  emailInput.addEventListener('blur', function() {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.value.trim()) {
          showError(this, 'Email is required');
      } else if (!emailRegex.test(this.value)) {
          showError(this, 'Please enter a valid email address');
      } else {
          clearError(this);
      }
  });

  passwordInput.addEventListener('blur', function() {
      if (!this.value.trim()) {
          showError(this, 'Password is required');
      } else if (this.value.length < 8) {
          showError(this, 'Password must be at least 8 characters');
      } else {
          clearError(this);
      }
  });
</script>

</body>
</html>
