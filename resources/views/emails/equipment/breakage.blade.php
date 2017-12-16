@component('mail::message')
# Hello!

This is just to let you know that a breakage has just been reported.

@component('mail::panel')
**Equipment:** {{ $breakage['name'] }}<br>
**Location:** {{ $breakage['location'] }}<br>
**Labelled as:** {{ $breakage['label'] }}<br>
**Reported by:** {{ $breakage['user_name'] }} ({{ $breakage['user_username'] }})<br>
**Description:**<br>
{{ $breakage['description'] }}
@endcomponent

@component('mail::button', ['color' => 'green', 'url' => route('equipment.repairs.view', ['id' => $breakage['id']])])
View breakage
@endcomponent

To get more details about this breakage, simply reply to this email.

Regards,<br>
{{ config('app.name') }}
@endcomponent
