@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'skills-index')
@section('header-main', 'Training Skills')
@section('title', 'Training Skills')

@section('content')
    <div class="tab-vertical" id="training-skill-categories" role="tabpanel">
        <div class="tab-links">
            <nav>
                <ul class="nav nav-pills nav-stacked category-list" role="tablist">
                    <li style="display: none !important;"><a role="button"></a></li>
                    @foreach ($SkillCategories as $category)
                        <li>
                            <a role="button">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
            @can('create', \App\Models\Training\Skills\Skill::class)
                <div>
                    <a class="btn btn-success" href="{{ route('training.skill.create') }}">
                        <span class="fa fa-plus"></span>
                        <span>Create Skill</span>
                    </a>
                </div>
            @endcan
        </div>
        <div class="tab-content">
            <div class="tab-pane active">
                @include('training.skills.panes.welcome')
            </div>
            @foreach ($SkillCategories as $category)
                <div class="tab-pane">
                    @include('training.skills.panes.category')
                </div>
            @endforeach
            <div class="btn-group">
                @can('apply', \App\Models\Training\Skills\Skill::class)
                    <a class="btn btn-success" href="{{ route('training.skill.apply.form') }}">
                        <span class="fa fa-plus"></span>
                        <span>Apply for Skill</span>
                    </a>
                @endcan
                @if (Auth::user()->isAdmin())
                    <a class="btn btn-success" href="{{ route('training.skill.award.form') }}">
                        <span class="fa fa-plus"></span>
                        <span>Award Skill</span>
                    </a>
                    <a class="btn btn-danger" href="{{ route('training.skill.revoke.form') }}">
                        <span class="fa fa-remove"></span>
                        <span>Revoke Skill</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
