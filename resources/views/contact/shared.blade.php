@extends('app.main')

@section('page-section', 'contact')
@section('header-main', 'Contact Us')

@section('content')
    <div class="tabpanel tab-group-perm">
        <div class="tab-links">
            {!! $menu !!}
        </div>
        <div class="tab-content">
            <div class="tab-pane active">
                @yield('tab')
            </div>
            <div class="tab-pane"></div>
            <div class="tab-pane"></div>
        </div>
    </div>
@endsection
