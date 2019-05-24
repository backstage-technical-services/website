@component('mail::message')
# Hello all,

The following near miss has been reported:

@component('mail::panel')
**Location**: {{ $report['location'] }}<br>
**Date and Time**: {{ $report['date']->tzUSer()->format('D jS M Y g:ia') }}<br>
**Details:**<br>{{ $report['details'] }}<br>
@if($report['safety_recommendations'])
**Safety Recommendations:**<br>{{ $report['safety_recommendations'] }}<br>
@endif
@endcomponent

@if($report['user_name'] || $report['user_email'])
The reporter's details are:

@component('mail::panel')
@if($report['user_name'])
**Name:** {{ $report['user_name'] }}<br>
@endif
@if($report['user_email'])
**Email:** {{ $report['user_email'] }}
@endif
@endcomponent
@endif

Regards,<br>
{{ config('app.name') }}
@endcomponent
