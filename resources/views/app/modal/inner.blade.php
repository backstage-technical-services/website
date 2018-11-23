<div class="modal-header">
    @if(isset($header))
        <h1>{!! $header ?? '' !!}</h1>
    @else
        @yield('modal.header')
    @endif
</div>
<div class="modal-body">
    @if(isset($content))
        {!! $content ?? '' !!}
    @else
        @yield('modal.content')
    @endif
</div>
<div class="modal-footer">
    @if(isset($footer))
        {!! $footer ?? '' !!}
    @else
        @yield('modal.footer')
    @endif
</div>