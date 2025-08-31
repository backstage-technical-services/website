@component('mail::message')
    # Hi {{ ucfirst($forename) }},

    Thank you for submitting your accident report. It has been sent to the relevant parties in Backstage and the Students'
    Union and a copy is included below for your reference.

    @include('emails.contact._accident')

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
