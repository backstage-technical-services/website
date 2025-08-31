@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'skills-award')
@section('title', ' Award Skill')
@section('header-main', 'Training Skills')
@section('header-sub', 'Revoke Skill')

@section('content')
    {!! Form::open() !!}
    <div class="form-group @InputClass('skill_id')">
        {!! Form::label('skill_id', 'Select skill:', ['class' => 'control-label']) !!}
        @if ($skill === null)
            {!! Form::select('skill_id', $SkillList, null, ['class' => 'form-control']) !!}
            @InputError('skill_id')
        @else
            <p class="form-control-static">{{ $skill->name }}</p>
            {!! Form::hidden('skill_id', $skill->id) !!}
        @endif
    </div>

    <div class="form-group @InputClass('level')">
        {!! Form::label('level', 'Select the level to award:', ['class' => 'control-label']) !!}
        {!! Form::select('level', $levels, null, ['class' => 'form-control']) !!}
        @InputError('level')
    </div>

    <div class="form-group @InputClass('members')">
        {!! Form::label('members', 'Select member(s):', ['class' => 'control-label']) !!}
        {!! Form::memberList('members[]', null, ['class' => 'form-control', 'select2' => true, 'multiple' => true]) !!}
        <p class="help-block alt">Any members with a lower skill level than selected will be ignored.</p>
        @InputError('members')
    </div>

    <div class="form-group">
        <button class="btn btn-success" data-disable="click" data-disable-text="Processing ...">
            <span class="fa fa-check"></span>
            <span>Revoke Skill</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('training.skill.index', 'Cancel') !!}
        </span>
    </div>

    {!! Form::close() !!}
@endsection
