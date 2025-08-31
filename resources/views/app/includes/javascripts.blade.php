<script src="/js/vendors.js"></script>
<script src="/js/app.js"></script>
@yield('javascripts')
{!! Notify::config() !!}
{!! NoCaptcha::renderJs() !!}
<script>
    @yield('scripts')
</script>
