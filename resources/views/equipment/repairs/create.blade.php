@extends('app.main')

@section('page-section', 'equipment')
@section('page-id', 'repairs-create')
@section('header-main', 'Repairs Database')
@section('header-sub', 'Report Breakage')
@section('title', 'Report Breakage')

@section('scripts')
    $('div.tabpanel').tabify();
@endsection

@section('content')
    <div class="tabpanel">
        <div class="tab-links">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#">Backstage Kit</a></li>
                <li><a href="#">EMP Audio Kit</a></li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane active">
                @include('equipment.repairs.create.backstage')
            </div>
            <div class="tab-pane">
                @include('equipment.repairs.create.emp_audio')
            </div>
        </div>
    </div>
@endsection
