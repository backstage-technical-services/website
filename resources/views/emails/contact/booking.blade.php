@component('mail::message')
# Hi all,

We recently received the following booking request:

@include('emails.contact._booking')

{{ $booking['contact_name'] }} can be contacted by replying to this email.
@endcomponent
