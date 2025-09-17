<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Two-Factor Authentication</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 flex items-center justify-center min-h-screen">

  <main class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 sm:p-8">
    <!-- Title -->
    <h1 class="text-2xl font-bold text-center mb-4">Two-Factor Authentication</h1>
    <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-6">
      Enter the 6-digit code sent to your email or phone
    </p>

    <!-- 2FA Code Inputs -->
<form action="{{ route('2fa-authenticate') }}" method="POST" class="space-y-5" id="twofa-form">
  @csrf
  <div class="flex justify-between gap-2">
    @for ($i = 0; $i < 6; $i++)
      <input type="text" maxlength="1"
        class="otp-input w-12 h-12 text-center text-lg font-bold rounded-md border border-gray-300 dark:border-gray-700 
               bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 outline-none"
        required>
    @endfor
  </div>

  <!-- Hidden final input -->
  <input type="hidden" name="two_factor_code" id="two_factor_code">

  <!-- Verify Button -->
  <button type="submit"
    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition">
    Verify Code
  </button>
</form>

    <!-- Resend -->
    <p class="text-center text-sm mt-6">
      Didn’t receive the code? 
      <a href="#" class="text-blue-600 hover:underline dark:text-blue-400">Resend</a>
    </p>
  </main>

  <script>
  const inputs = document.querySelectorAll('.otp-input');
  const hiddenInput = document.getElementById('two_factor_code');

  inputs.forEach((input, idx) => {
    input.addEventListener('input', () => {
      // Move to next input automatically
      if (input.value && idx < inputs.length - 1) {
        inputs[idx + 1].focus();
      }
      // Combine all digits into hidden input
      hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
    });

    input.addEventListener('keydown', (e) => {
      // Backspace → move back
      if (e.key === "Backspace" && !input.value && idx > 0) {
        inputs[idx - 1].focus();
      }
    });
  });
</script>

</body>
</html>
