@component('mail::message')
    # Hi {{ $forename }},

    This is an automated response to confirm your booking request, included below for your reference.

    @include('emails.contact._booking')

    Please note that this is **not** a confirmation that we can support your event; you should receive a response within a
    few business days, however over busy periods we may take longer to get back to you.

    If you haven't received a response from us, you can contact the Production Manager directly by replying to this email.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
