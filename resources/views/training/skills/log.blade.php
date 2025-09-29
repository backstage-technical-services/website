@extends('app.main')

@section('page-section', 'training')
@section('page-id', 'skills-log')
@section('title', 'Skills Log')
@section('header-main', 'Training Skills')
@section('header-sub', 'Skills Log')

@section('content')
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="skill-date">Date</th>
                <th class="skill-name">Skill</th>
                <th class="skill-user">Member</th>
                <th class="skill-level">Skill Level</th>
                <th class="skill-user">Awarded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($skills as $skill)
                <tr>
                    <td class="skill-date dual-layer">
                        <div class="upper">{{ $skill->updated_at->format('d/m/Y') }}</div>
                        <div class="lower">{{ $skill->updated_at->format('H:i:s') }}</div>
                    </td>
                    <td class="dual-layer">
                        <div class="upper">
                            <a class="grey"
                               href="{{ route('training.skill.view', ['id' => $skill->skill_id]) }}"
                               target="_blank">
                                {{ $skill->skill->name }}
                            </a>
                        </div>
                        <div class="lower">
                            {{ $skill->skill->category_name }}
                        </div>
                    </td>
                    <td class="skill-user dual-layer">
                        <div class="upper">
                            <a class="grey"
                               href="{{ route('member.view', ['username' => $skill->user->username, 'tab' => 'training']) }}"
                               target="_blank">
                                {{ $skill->user->name }}
                            </a>
                        </div>
                        <div class="lower">
                            {{ $skill->user->username }}
                        </div>
                    </td>
                    <td class="skill-level">
                        @include('training.skills.proficiency', ['level' => $skill->level])
                    </td>
                    <td class="skill-user dual-layer">
                        <div class="upper">
                            <a class="grey"
                               href="{{ route('member.view', ['username' => $skill->awarder->username]) }}"
                               target="_blank">
                                {{ $skill->awarder->name }}
                            </a>
                        </div>
                        <div class="lower">
                            {{ $skill->awarder->username }}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No skills</td>
                </tr>
            @endif
        </tbody>
    </table>
    @Paginator($skills)
@endsection