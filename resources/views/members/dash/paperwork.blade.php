@if($event->paperwork[$paperwork])
    <span class="fa fa-check success" title="Completed"></span>
@else
    <span class="fa fa-remove danger" title="Needs completing"></span>
@endif