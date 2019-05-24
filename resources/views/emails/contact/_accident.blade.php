@component('mail::panel')
## Accident details
**Location:** {{ $report['location'] }}<br>
**Date:** {{ $report['date_formatted']->tzUser()->format('D jS M Y g:ia') }}<br>
**Details:**<br>{{ $report['details'] }}<br>
**Severity:** {{ $report['severity_email'] }}<br>
@if(@$report['absence_details'])
**Absence Details:**<br>{{ $report['absence_details'] }}<br>
@endif

## Injured party
**Name:** {{ $report['injured_name'] }}<br>
**Category:** {{ $report['person_type_email'] }}<br>

## Contact details
**Name:** {{ $report['contact_name'] }}<br>
**Email:** [{{ $report['contact_email'] }}](mailto:{{ $report['contact_email'] }})<br>
**Phone:** {{ $report['contact_phone'] }}<br>
@endcomponent