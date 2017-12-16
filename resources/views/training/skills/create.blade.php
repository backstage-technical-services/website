@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'skills-create')
@section('title', 'Training Skills')
@section('header-main', 'Training Skills')
@section('header-sub', 'Create a Skill')

@section('content')
    {!! Form::model($skill, ['route' => 'training.skill.store']) !!}
    @include('training.skills.form')
    <div>
        <button class="btn btn-success" data-disable="click" data-disable-text="Creating skill ...">
            <span class="fa fa-plus"></span>
            <span>Create skill</span>
        </button>
        <span class="form-link">
            or {!! link_to_route('training.skill.index', 'Cancel') !!}
        </span>
    </div>
    {!! Form::close() !!}
@endsection
