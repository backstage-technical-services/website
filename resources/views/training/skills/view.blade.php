@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'skills-view')
@section('title', $skill->name . ' :: Training Skills')
@section('header-main', 'Training Skills')

@section('content')
    <h2>
        {{ $skill->name }}
        @if(Auth::user()->hasApplicationPending($skill))
            <span class="badge">application pending</span>
        @endif
    </h2>
    <h4 class="category">[{{ $skill->category ? $skill->category->name : 'Uncategorised' }}]</h4>
    <div class="description">{!! Markdown::convertToHtml($skill->description) !!}</div>
    <h3>Level Requirements</h3>
    <table class="table table-striped level-requirements">
        <tbody>
            @for($i = 1; $i <= 3; $i++)
                <tr>
                    <td>
                        @include('training.skills.proficiency', ['level' => $i])
                        <br>
                        <small>{{ $LevelNames[$i] }}</small>
                    </td>
                    <td>
                        @if($skill->{'level'.$i} !== null)
                            {!! Markdown::convertToHtml($skill->{'level'.$i}) !!}
                        @else
                            <p class="em">Level not available</p>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
    <h3>Members With This Skill</h3>
    <table class="table table-striped" id="member-list">
        <thead>
            <tr>
                <th>@include('training.skills.proficiency', ['level' => 3])</th>
                <th>@include('training.skills.proficiency', ['level' => 2])</th>
                <th>@include('training.skills.proficiency', ['level' => 1])</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < max(count(max($skill->users)), 1); $i++)
                <tr>
                    @for($j = 3; $j >= 1; $j--)
                        <td>
                            @if(isset($skill->users[$j][$i]))
                                {!! link_to_route('member.view', $skill->users[$j][$i]->name, ['username' => $skill->users[$j][$i]->username, 'tab' => 'training']) !!}
                            @elseif($i == 0)
                                <div class="text-center em">no members</div>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
    <div class="btn-group">
        @can('edit', $skill)
            <div class="btn-group">
                <a class="btn btn-warning" href="{{ route('training.skill.edit', ['id' => $skill->id]) }}">
                    <span class="fa fa-pencil"></span>
                    <span>Edit</span>
                </a>
                @can('delete', $skill)
                    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button data-submit-ajax="{{ route('training.skill.destroy', $skill->id) }}"
                                    data-submit-confirm="Are you sure you want to delete this skill?"
                                    data-redirect="true"
                                    data-redirect-location="{{ route('training.skill.index') }}"
                                    type="button">
                                <span class="fa fa-trash"></span> Delete
                            </button>
                        </li>
                    </ul>
                @endcan
            </div>
        @endcan
        @if(Auth::user()->can('apply', $skill) && Auth::user()->getSkillLevel($skill) < 3)
            <a class="btn btn-success" href="{{ route('training.skill.apply.form', ['id' => $skill->id]) }}">
                <span class="fa fa-plus"></span>
                <span>Apply for a level</span>
            </a>
        @endcan
        @can('award', $skill)
            <div class="btn-group">
                <a class="btn btn-success" href="{{ route('training.skill.award.form', ['id' => $skill->id]) }}">
                    <span class="fa fa-user-plus"></span>
                    <span>Award level</span>
                </a>
                @can('revoke', $skill)
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{ route('training.skill.revoke.form', ['id' => $skill->id]) }}">
                                <span class="fa fa-user-times"></span> Revoke
                            </a>
                        </li>
                    </ul>
                @endcan
            </div>
        @endcan
    </div>
@endsection