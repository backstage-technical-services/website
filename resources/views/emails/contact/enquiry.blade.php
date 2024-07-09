@component('mail::message')
# Hello!

We have recently received an enquiry from {{ $enquiry['name'] }}:

@component('mail::panel')
{{ $enquiry['message'] }}
@endcomponent

{{ ucfirst($forename) }} can be contacted by:

**Email:**  [{{ $enquiry['email'] }}](mailto:{{ $enquiry['email'] }})

@if(isset($enquiry['phone']) && @$enquiry['phone'])
**Phone:** {{ $enquiry['phone'] }}
@endif

To respond to this enquiry, simply reply to this email.

Regards,<br>
{{ config('app.name') }}
@endcomponent
