@extends('app.main')

@section('page-section', 'events')
@section('page-id', 'create')
@section('header-main', 'Events')
@section('header-sub', 'Create Event')
@section('title', 'Create Event')

@section('content')
    {!! Form::model(new \App\Models\Events\Event()) !!}
    <div class="form-group @InputClass('name')">
        {!! Form::label('name', 'Event Name:', ['class' => 'control-label']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'What is the event called?']) !!}
        @InputError('name')
    </div>
    <div class="form-group @InputClass('em_id')">
        {!! Form::label('em_id', 'Event Manager:', ['class' => 'control-label']) !!}
        {!! Form::memberList('em_id', null, ['class' => 'form-control', 'select2' => true, 'include_blank' => true]) !!}
        @InputError('em_id')
        <p class="help-block alt">Leave blank if there isn't an Event Manager at the moment</p>
    </div>
    <div class="form-group @InputClass('type')">
        {!! Form::label('type', 'Event Type:', ['class' => 'control-label']) !!}
        {!! Form::select('type', \App\Models\Events\Event::$Types, null, ['class' => 'form-control', 'data-type' => 'toggle-visibility']) !!}
        @InputError('type')
    </div>
    <div class="form-group @InputClass('description')">
        {!! Form::label('description', 'Description:', ['class' => 'control-label']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Briefly describe what the event is about']) !!}
        @InputError('description')
        <p class="help-block alt">This will be visible to the public</p>
    </div>
    <div class="form-group @InputClass('venue')">
        {!! Form::label('venue', 'Venue:', ['class' => 'control-label']) !!}
        {!! Form::text('venue', null, ['class' => 'form-control', 'placeholder' => 'Where is it?']) !!}
        @InputError('venue')
    </div>
    <div class="form-group @InputClass('venue_type')" data-visibility-input="type" data-visibility-value="{{ \App\Models\Events\Event::TYPE_EVENT }}">
        {!! Form::label('venue_type', 'Venue Type:', ['class' => 'control-label']) !!}
        {!! Form::select('venue_type', \App\Models\Events\Event::$VenueTypes, null, ['class' => 'form-control']) !!}
        @InputError('venue_type')
    </div>
    <div class="form-group @InputClass('client_type')" data-visibility-input="type" data-visibility-value="{{ \App\Models\Events\Event::TYPE_EVENT }}">
        {!! Form::label('client_type', 'Client Type:', ['class' => 'control-label']) !!}
        {!! Form::select('client_type', \App\Models\Events\Event::$Clients, null, ['class' => 'form-control']) !!}
        @InputError('client_type')
    </div>
    <div class="form-group @InputClass('date_start') @InputClass('date_end')">
        {!! Form::label('date_start', 'Date:', ['class' => 'control-label']) !!}
        <div class="row">
            <div class="col-xs-5">
                {!! Form::date('date_start', null, ['class' => 'form-control', 'placeholder' => 'yyyy-mm-dd']) !!}
            </div>
            <div class="col-xs-2" data-visibility-input="one_day" data-visibility-state="unchecked">
                <p class="form-control-static text-center">to</p>
            </div>
            <div class="col-xs-5" data-visibility-input="one_day" data-visibility-state="unchecked">
                {!! Form::date('date_end', null, ['class' => 'form-control', 'placeholder' => 'yyyy-mm-dd']) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5">@InputError('date_start')</div>
            <div class="col-xs-2" data-visibility-input="one_day" data-visibility-state="unchecked"></div>
            <div class="col-xs-5" data-visibility-input="one_day" data-visibility-state="unchecked">@InputError('date_end')</div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('one_day', 1, null, ['data-type' => 'toggle-visibility']) !!}
                        This is a one-day event
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group @InputClass('time_start') @InputClass('time_end')">
        {!! Form::label('time_start', 'Time:', ['class' => 'control-label']) !!}
        <div class="row">
            <div class="col-xs-5">
                {!! Form::time('time_start', '19:00', ['class' => 'form-control', 'placeholder' => 'hh:mm', 'data-date-format' => 'HH:mm']) !!}
            </div>
            <div class="col-xs-2">
                <p class="form-control-static text-center">to</p>
            </div>
            <div class="col-xs-5">
                {!! Form::time('time_end', '22:30', ['class' => 'form-control', 'placeholder' => 'hh:mm', 'data-date-format' => 'HH:mm']) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5">@InputError('time_start')</div>
            <div class="col-xs-2"></div>
            <div class="col-xs-5">@InputError('time_end')</div>
        </div>
    </div>
    <div class="form-group @InputClass('production_charge')" data-visibility-input="type" data-visibility-value="{{ \App\Models\Events\Event::TYPE_EVENT }}">
        {!! Form::label('production_charge', 'Production Charge:', ['class' => 'control-label']) !!}
        <div class="input-group">
            <span class="input-group-addon">
                <span class="fa fa-gbp"></span>
            </span>
            {!! Form::text('production_charge', null, ['class' => 'form-control']) !!}
        </div>
        @InputError('production_charge')
    </div>
    <div class="form-group" id="buttons">
        <div class="btn-group">
            <button class="btn btn-success" data-disable="click" data-disable-text="Creating event..." name="action" value="create">
                <span class="fa fa-check"></span>
                <span>Create event</span>
            </button>
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <button name="action" value="create-another">
                        <span class="fa fa-plus"></span>
                        <span>Create another event after</span>
                    </button>
                </li>
            </ul>
        </div>
        <span class="form-link">
                or {!! link_to_route('event.index', 'Cancel') !!}
            </span>
    </div>
    {!! Form::close() !!}
@endsection