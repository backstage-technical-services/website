<div class="date-header">
    <a class="prev" href="{{ $month_prev }}">
        <span class="fa fa-caret-left"></span>
    </a>
    <span class="month">{{ $date->format('F Y') }}</span>
    <a class="next" href="{{ $month_next }}">
        <span class="fa fa-caret-right"></span>
    </a>
</div>
