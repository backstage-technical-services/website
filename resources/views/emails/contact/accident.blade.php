@component('mail::message')
    # Hello all,

    The following accident has been reported.

    @include('emails.contact._accident')

    To contact the contact person, simply reply to this email.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
