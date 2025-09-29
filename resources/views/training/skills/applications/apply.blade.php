@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'applications-apply')
@section('title', 'Apply for a Skill')
@section('header-main', 'Training Skills')
@section('header-sub', 'Apply for a Skill')

@section('content')
    {!! Form::open(['route' => 'training.skill.apply']) !!}
    <p>If you feel like you should be awarded a skill, or be a higher level than currently awarded, you can use this form to apply for your new skill level.
        This will then be reviewed by the Training and Safety Officer.</p>
    <div class="form-group @InputClass('skill_id')">
        {!! Form::label('skill_id', 'Select skill:', ['class' => 'control-label']) !!}
        @if($skill === null)
            {!! Form::select('skill_id', $SkillList, null, ['class' => 'form-control']) !!}
            @InputError('skill_id')
        @else
            <p class="form-control-static">{{ $skill->name }}</p>
            {!! Form::hidden('skill_id', $skill->id) !!}
        @endif
    </div>
    <div class="form-group @InputClass('level')">
        {!! Form::label('level', 'Select level to apply for:', ['class' => 'control-label']) !!}
        <select class="form-control" name="level">
            @for($i = 1; $i <= 3; $i++)
                @if(!$skill || $skill->isLevelAvailable($i))
                    <option value="{{ $i }}"{{ !in_array($i, $AvailableLevels) ? ' disabled' : '' }}>{{ $LevelNames[$i] }}</option>
                @endif
            @endfor
        </select>
        @InputError('level')
    </div>
    <div class="form-group @InputClass('reasoning')">
        {!! Form::label('reasoning', 'Please provide some reasoning:', ['class' => 'control-label']) !!}
        {!! Form::textarea('reasoning', null, ['class' => 'form-control', 'placeholder' => 'Explain why you should be awarded this level - use examples to help justify your application', 'rows' => 6]) !!}
        @InputError('reasoning')
    </div>
    <div class="form-group">
        <button class="btn btn-success" data-disable="click" data-disable-text="Submitting application ...">
            <span class="fa fa-check"></span>
            <span>Apply for Skill</span>
        </button>
        <span class="form-link">
            or
            @if($skill !== null)
                {!! link_to_route('training.skill.view', 'Cancel', ['id' => $skill->id]) !!}
            @else
                {!! link_to_route('training.skill.index', 'Cancel') !!}
            @endif
        </span>
    </div>
    {!! Form::close() !!}
@endsection