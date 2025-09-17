@component('mail::message')
# 🔐 Verify Your Login

Hi {{ $user->name ?? 'User' }},

For your security, we use **Two-Factor Authentication (2FA)**. Use the code below to complete your login:

@component('mail::panel')
<span style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">{{ $two_factor_code }}</span>
@endcomponent

This code will expire in **5 minutes**.

If you **did not request** this login or feel something’s suspicious, please ignore this email or [contact our support team](mailto:s.tabarno.jianlester@cmu.edu.ph) immediately.

---

Thanks for helping keep your account secure.  
**– Weather App**

@endcomponent
