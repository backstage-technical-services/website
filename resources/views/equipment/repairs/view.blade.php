@extends('app.main')

@section('page-section', 'equipment')
@section('page-id', 'repairs-view')
@section('header-main', 'Repairs Database')
@section('header-sub', 'View Breakage')
@section('title', 'View Breakage')

@section('content')
    @if($breakage->closed)
        <div class="closed">
            <span class="label label-danger">marked as closed</span>
        </div>
    @endif
    {!! Form::model($breakage, ['class' => 'form-horizontal']) !!}
        <div class="form-group">
            {!! Form::label('name', 'Item:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                <p class="form-control-static">{{ $breakage->name }}</p>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('location', 'Location:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                <p class="form-control-static">{{ $breakage->location }}</p>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('label', 'Labelled as:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                <p class="form-control-static">{{ $breakage->label }}</p>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('description', 'Details:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                <p class="form-control-static">{!! nl2br($breakage->description) !!}</p>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('user_id', 'Reported by:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                <p class="form-control-static">{!! $breakage->user ? ($breakage->user->name . ' (' . $breakage->user->username . ')') : '<em>- unknown -</em>' !!}
                    <br>{{ $breakage->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="form-group @InputClass('comment')">
            {!! Form::label('comment', 'E&S Comment:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                @if(Auth::user()->can('update', $breakage))
                    {!! Form::textarea('comment', null, ['class' => 'form-control', 'rows' => 4]) !!}
                    @InputError('comment')
                @else
                    <p class="form-control-static">{!! $breakage->comment ? nl2br($breakage->comment) : '<em>- none -</em>' !!}</p>
                @endif
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('images', 'Images:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                @if($breakage->images->count())
                    <div class="row">
                        @foreach($breakage->images as $image)
                            <div class="col-xs-6 col-md-4">
                                <a href="{{ $image->getImageRoute() }}" class="thumbnail" target="_blank">
                                    <img src="{{ $image->getImageRoute() }}" alt="Image">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="form-control-static"><em>- none -</em></p>
                @endif
            </div>

        </div>

        {{-- Status --}}
        <div class="form-group @InputClass('status')">
            {!! Form::label('status', 'Status:', ['class' => 'control-label col-md-4']) !!}
            <div class="col-md-8">
                @if(Auth::user()->can('update', $breakage))
                    {!! Form::select('status', \App\Models\Equipment\Breakage::$Status, null, ['class' => 'form-control']) !!}
                    @InputError('status')
                @else
                    <p class="form-control-static">{{ \App\Models\Equipment\Breakage::$Status[$breakage->status] }}</p>
                @endif
            </div>
        </div>

        {{-- Buttons --}}
        <div class="form-group">
            <div class="col-md-4"></div>
            <div class="col-md-8">
                @if(Auth::user()->can('update', $breakage))
                    <div class="btn-group">
                        <button class="btn btn-success" disable-submit="Updating ..." name="action" value="update">
                            <span class="fa fa-check"></span>
                            <span>Update</span>
                        </button>
                        @if($breakage->closed)
                            <button class="btn btn-primary" disable-submit="Updating ..." name="action" value="reopen">
                                <span class="fa fa-repeat"></span>
                                <span>Re-open</span>
                            </button>
                        @else
                            <button class="btn btn-danger" disable-submit="Updating ..." name="action" value="close">
                                <span class="fa fa-times"></span>
                                <span>Close</span>
                            </button>
                        @endif
                    </div>
                    <span class="form-link">
                        or {!! link_to_route('equipment.repairs.index', 'Back') !!}
                    </span>
                @else
                    <a class="btn btn-danger" href="{{ route('equipment.repairs.index') }}">
                        <span class="fa fa-long-arrow-left"></span>
                        <span>Back</span>
                    </a>
                @endif
            </div>
        </div>
    {!! Form::close() !!}
@endsection