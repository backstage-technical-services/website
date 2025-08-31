@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'skills-create')
@section('title', 'Edit ' . $skill->name . ' :: Training Skills')
@section('header-main', 'Training Skills')
@section('header-sub', 'Edit Skill')

@section('content')
    {!! Form::model($skill, ['route' => ['training.skill.update', $skill->id]]) !!}
    @include('training.skills.form')
    <div>
        <div class="btn-group">
            <button class="btn btn-success" data-disable="click" data-disable-text="Saving ...">
                <span class="fa fa-check"></span>
                <span>Save Changes</span>
            </button>
            <button
                class="btn btn-danger"
                data-submit-ajax="{{ route('training.skill.destroy', $skill->id) }}"
                data-submit-confirm="Are you sure you want to delete this skill?"
                data-redirect="true"
                data-redirect-location="{{ route('training.skill.index') }}"
                type="button"
            >
                <span class="fa fa-remove"></span>
                <span>Delete</span>
            </button>
        </div>
        <span class="form-link">
            or {!! link_to_route('training.skill.view', 'Cancel', ['id' => $skill->id]) !!}
        </span>
    </div>
    {!! Form::close() !!}
@endsection
