<!DOCTYPE html>
<html lang="en">
    <head>
        @include('app.includes.head')
    </head>
    <body page-section="@yield('page-section')" page-id="@yield('page-id')">
        <!-- Persistent messages -->
        <div class="message-centre" id="message-centre-upper">
            {!! Notify::renderBag('permanent') !!}
        </div>
        <!-- Main site wrapper -->
        <main id="site-wrapper">
            <!-- Header -->
            <div id="header-wrapper">
                <img src="/images/bts-logo.png">
            </div>
            <!-- Main menu -->
            <div id="menu-wrapper">
                @include('app.includes.menu')
            </div>
            <!-- Content -->
            <div id="content-wrapper">
                <!-- Main messages -->
                <div class="message-centre" id="message-centre-main">
                    <div class="message-centre-inner">
                        @yield('messages')
                        {!! Notify::renderBag('default') !!}
                    </div>
                </div>
                @hasSection('header-main')
                    <h1 class="page-header">@yield('header-main')</h1>
                @endif
                @hasSection('header-sub')
                    <h2 class="page-header">@yield('header-sub')</h2>
                @endif
                <div id="content">
                    @yield('content')
                </div>
            </div>
        </main>
        <!-- Footer -->
        <div id="footer">
            @include('app.includes.footer')
        </div>

        @include('app.includes.modal')
        @include('app.includes.javascripts')
    </body>
</html>