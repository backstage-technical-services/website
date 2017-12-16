@component('mail::message')
# Hi Alison,

This email has been generated to inform you that Backstage has accepted a new off-campus booking with an external client. The details are as follows:

@component('mail::panel')
**Event Name:** {{ $event_name }}<br>
**Event Date(s):** {{ $event_dates }}<br>
**Backstage Event Manager:** {{ $em }}<br>
**Client:** {{ $client }}<br>
**Location:** {{ $venue_type }}<br>
**Venue:** {{ $venue }}<br>
**Additional Information:**<br>
{{ $description }}
@endcomponent

If you have any questions about this event, reply to this email to contact the Backstage Committee.

Regards,<br>
{{ config('app.name') }}
@endcomponent
