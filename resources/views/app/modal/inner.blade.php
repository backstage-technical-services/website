<div class="modal-header">
    @if(isset($header))
        <h1>{!! $header or '' !!}</h1>
    @else
        @yield('modal.header')
    @endif
</div>
<div class="modal-body">
    @if(isset($content))
        {!! $content or '' !!}
    @else
        @yield('modal.content')
    @endif
</div>
<div class="modal-footer">
    @if(isset($footer))
        {!! $footer or '' !!}
    @else
        @yield('modal.footer')
    @endif
</div>