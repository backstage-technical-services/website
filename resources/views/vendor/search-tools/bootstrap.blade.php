<div class="search-tools pull-right">
    @if($ShowTools['filter'] && count($FilterOptions))
        <div class="dropdown filter">
            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Filter">
                <span class="fa fa-filter"></span>
            </button>
            <ul class="dropdown-menu">
                @foreach($FilterOptions as $option)
                    <li{{ $option->value == $FilterValue ? ' class=current' : '' }}>
                        <a href="{{ url($option->url) }}">{{ $option->text }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    @if($ShowTools['search'])
        <div class="dropdown search">
            <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Search">
                <span class="fa fa-search"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <input class="form-control"
                           data-base-url="{{ $BaseURL }}"
                           data-base-query="{{ json_encode($BaseQuery) }}"
                           type="text"
                           value="{{ $SearchValue ?? '' }}">
                    @if($SearchValue)
                        <a class="clear-search" href="{{ $ClearSearchLink }}" title="Clear search">
                            <span class="fa fa-remove"></span>
                        </a>
                    @endif
                </li>
            </ul>
        </div>
    @endif
</div>