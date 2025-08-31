@component('mail::message')
    # Hello!

    We have just been sent the following feedback for the event **{{ $feedback['event'] }}**.

    @component('mail::panel')
        {{ $feedback['feedback'] }}
    @endcomponent

    To protect their anonymity, their contact details are not included.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
