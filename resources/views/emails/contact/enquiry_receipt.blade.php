@component('mail::message')
    # Hello!

    This is just to confirm that we've received your recent enquiry, which we've included below for your reference.

    @component('mail::panel')
        {{ $enquiry['message'] }}
    @endcomponent

    We aim to respond to enquiries within 2 business days, however over busy periods we may take longer to get back to you.
    If you haven't received a response from us, you can get in contact directly by replying to this email.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
