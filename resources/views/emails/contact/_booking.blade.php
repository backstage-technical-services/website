@component('mail::panel')
## Event details
**Event Name:** {{ $booking['event_name'] }}<br>
**Dates:** {{ $booking['event_dates'] }}<br>
@if(@$booking['show_time'])
**Show Time:** {{ $booking['show_time'] }}<br>
@endif
**Venue:** {{ $booking['event_venue'] }}<br>
**Venue Access:** {{ str_replace(['0', '1', '2'], ['Morning', 'Afternoon', 'Evening'], implode(', ', $booking['event_access'])) }}<br>
@if(@$booking['event_description'])
**Description:**<br>
{{ $booking['event_description'] }}<br>
@endif

## Contact details
**Client:** {{ $booking['event_club'] }}<br>
**Contact:** {{ $booking['contact_name'] }}<br>
**Email:** {{ $booking['contact_email'] }}<br>
@if(@$booking['contact_phone'])
**Phone:** {{ $booking['contact_phone'] }}<br>
@endif

@if(@$booking['additional'])
## Additional info
{{ $booking['additional'] }}
@endif
@endcomponent