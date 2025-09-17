@component('mail::message')
# 📧 Email Verification Required

Hi {{ $user->first_name }},

Thank you for registering with **Weather App**.  
To complete your registration and activate your account, please verify your email address by clicking the button below:

@component('mail::button', ['url' => url("/verify-email/" . $user->verification_token), 'color' => 'success'])
✅ Verify My Email
@endcomponent

This helps us ensure the security of your account.

> If you didn’t create an account, please ignore this email.

Thanks,<br>
**- Weather App**
@endcomponent
