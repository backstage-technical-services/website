@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'applications-view')
@section('title', 'Training Skills')
@section('header-main', 'Training Skills')
@section('header-sub', 'Application')

@section('content')
    {!! Form::open(['class' => 'form-horizontal']) !!}

    {{-- Member --}}
    <div class="form-group">
        {!! Form::label('user', 'Member:', ['class' => 'col-sm-4 control-label']) !!}
        <div class="col-sm-8">
            <p class="form-control-static">
                <a class="grey"
                   href="{{ route('member.view', ['username' => $application->user->username, 'tab' => 'training']) }}"
                   target="_blank">{{ $application->user->name }}</a> ({{ $application->user->username }})
            </p>
        </div>
    </div>

    {{-- Skill --}}
    <div class="form-group">
        {!! Form::label('skill', 'Skill:', ['class' => 'col-sm-4 control-label']) !!}
        <div class="col-sm-8">
            <p class="form-control-static">
                <a class="grey"
                   href="{{ route('training.skill.view', ['id' => $application->skill->id]) }}"
                   target="_blank">{{ $application->skill->name }}</a>
                <br>({{ $application->skill->category_name }})
            </p>
        </div>
    </div>

    {{-- Level --}}
    <div class="form-group">
        {!! Form::label('applied_level', 'Level Requested:', ['class' => 'col-sm-4 control-label']) !!}
        <div class="col-sm-8">
            <p class="form-control-static">
                @include('training.skills.proficiency', ['level' => $application->applied_level])
            </p>
        </div>
    </div>

    {{-- Current level --}}
    @if(!$application->isAwarded())
        <div class="form-group">
            {!! Form::label('current_level', 'Current Level:', ['class' => 'col-sm-4 control-label']) !!}
            <div class="col-sm-8">
                <p class="form-control-static">
                    @if($application->user->hasSkill($application->skill))
                        @include('training.skills.proficiency', ['level' => $application->user->getSkillLevel($application->skill)])
                    @else
                        <em>- none -</em>
                    @endif
                </p>
            </div>
        </div>
    @endif

    {{-- Reason --}}
    <div class="form-group">
        {!! Form::label('reasoning', 'Reasoning:', ['class' => 'col-sm-4 control-label']) !!}
        <div class="col-sm-8">
            {!! Markdown::convertToHtml($application->reasoning) !!}
        </div>
    </div>

    {{-- Awarded Level --}}
    <div class="form-group @InputClass('awarded_level')">
        {!! Form::label('awarded_level', 'Awarded Level:', ['class' => 'col-sm-4 control-label']) !!}
        <div class="col-sm-8">
            @if($application->isAwarded())
                <p class="form-control-static">
                    @if($application->awarded_level == 0)
                        <em>&ndash; no level awarded &ndash;</em>
                    @else
                        @include('training.skills.proficiency', ['level' => $application->awarded_level])
                    @endif
                </p>
            @else
                {!! Form::select('awarded_level', $levels, $application->applied_level, ['class' => 'form-control']) !!}
                @InputError('awarded_level')
            @endif
        </div>
    </div>

    {{-- Comment --}}
    <div class="form-group @InputClass('awarded_comment')">
        {!! Form::label('awarded_comment', 'Comment:', ['class' => 'col-sm-4 control-label']) !!}
        <div class="col-sm-8">
            @if($application->isAwarded())
                @if($application->awarded_comment)
                    {!! Markdown::convertToHtml($application->awarded_comment) !!}
                @else
                    <p class="form-control-static">
                        <em>&ndash; no comment provided &ndash;</em>
                    </p>
                @endif
            @else
                {!! Form::textarea('awarded_comment', null, ['class' => 'form-control', 'placeholder' => 'This is optional if you award a level, but nice to provide', 'rows' => 4]) !!}
                @InputError('awarded_comment')
            @endif
        </div>
    </div>

    {{-- Awarded details --}}
    @if($application->isAwarded())
        <div class="form-group">
            {!! Form::label('awarded', 'Awarded by:', ['class' => 'col-sm-4 control-label']) !!}
            <div class="col-sm-8">
                <p class="form-control-static">
                    {{ $application->awarder->name }}<br>{{ $application->awarded_date->diffForHumans() }}
                </p>
            </div>
        </div>
    @endif

    {{-- Buttons --}}
    <div class="form-group">
        <div class="col-sm-4"></div>
        <div class="col-sm-8">
            @if($application->isAwarded())
                <a class="btn btn-success" href="{{ route('training.skill.application.index') }}">
                    <span class="fa fa-long-arrow-left"></span>
                    <span>Back to the index</span>
                </a>
            @else
                <div class="btn-group">
                    <button class="btn btn-success" data-disable="click" data-disable-text="Processing ...">
                        <span class="fa fa-check"></span>
                        <span>Submit</span>
                    </button>
                    <a class="btn btn-primary"
                       data-toggle="modal"
                       data-target="#modal"
                       data-modal-template="skill_details"
                       data-modal-title="Skill Details"
                       href="#">
                        <span class="fa fa-question"></span>
                        <span>Skill details</span>
                    </a>
                </div>
                <span class="form-link">
                    or {!! link_to_route('training.skill.application.index', 'Cancel') !!}
                </span>
            @endif
        </div>
    </div>

    {!! Form::close() !!}
@endsection

@section('modal')
    @if(!$application->isAwarded())
        <div data-type="modal-template" data-id="skill_details">
            <div class="modal-body">
                {!! Form::open(['class' => 'form-horizontal']) !!}
                {{-- Name --}}
                <div class="form-group">
                    {!! Form::label('skill_name', 'Name:', ['class' => 'col-sm-4 control-label']) !!}
                    <div class="col-sm-8">
                        <p class="form-control-static">{{ $application->skill->name }}</p>
                    </div>
                </div>

                {{-- Category --}}
                <div class="form-group">
                    {!! Form::label('skill_category', 'Category:', ['class' => 'col-sm-4 control-label']) !!}
                    <div class="col-sm-8">
                        <p class="form-control-static">{!! $application->skill->category_name !!}</p>
                    </div>
                </div>

                {{-- Description --}}
                <div class="form-group">
                    {!! Form::label('skill_description', 'Description:', ['class' => 'col-sm-4 control-label']) !!}
                    <div class="col-sm-8">
                        <p class="form-control-static">{!! Markdown::convertToHtml($application->skill->description) !!}</p>
                    </div>
                </div>

                {{-- Level requirements --}}
                <h2 style="font-size: 18px;">Level Requirements</h2>
                <table class="table level-requirements">
                    @for($i = 1; $i <= 3; $i++)
                        <tr>
                            <td>@include('training.skills.proficiency', ['level' => $i])</td>
                            <td>{!! Markdown::convertToHtml($application->skill->{'level' . $i}) !!}</td>
                        </tr>
                    @endfor
                </table>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" data-toggle="modal" data-target="#modal" type="button">
                    <span class="fa fa-check"></span>
                    <span>Ok, got it</span>
                </button>
            </div>
        </div>
    @endif
@endsection