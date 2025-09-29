@component('mail::message')
# Hi,

Your skill application for **{{ $level }}** in **{{ $skill }}** has been processed by {{ $awarder }}.

@if($awarded_level == 0)
Unfortunately you haven't been awarded the skill this time. {{ $awarder_forename }}'s comments are included below.
@else
You have been awarded {{ $awarded_level_text }}.
@if($awarded_comment)
{{ $awarder_forename }} gave you some comments, which are included below.
@endif
@endif

@if($awarded_comment)
@component('mail::panel')
{{ $awarded_comment }}
@endcomponent
@endif

If you have any questions about this application, you can get in touch with the Training and Safety Officer by replying to this email.

Regards,<br>
{{ config('app.name') }}
@endcomponent