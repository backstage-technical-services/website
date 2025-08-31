@component('mail::message')
    # Hi {{ $name }},

    Please fill in the finance summary found at the link below for **{{ $event }}**, this will allow me to invoice
    the event quickly and efficiently.

    @component('mail::button', ['url' => config('bts.bts.finance_db.em_finance_url') . $event_id])
        EM Finance
    @endcomponent

    If you have any questions, you can contact the Treasurer by replying to this email.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
