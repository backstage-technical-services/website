@component('mail::message')
    # Hi {{ $name }},

    Due to the nature of your event (**{{ $event }}**) I would like you to fill in the following online budget
    tracker as you plan your event, this will allow me to keep a better eye on the events finances and process yellow forms
    quickly.

    @component('mail::button', ['url' => config('bts.bts.finance_db.em_finance_url') . $event_id])
        EM Finance
    @endcomponent

    If you have any questions, you can contact the Treasurer by replying to this email.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
