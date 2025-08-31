<table class="table table-striped">
    <thead>
        <th class="application-status"></th>
        <th class="application-skill">Skill</th>
        <th class="application-user">User</th>
        <th class="skill-level">Applied for</th>
        <th class="skill-level">Awarded</th>
        <th class="awarded-user">Awarded by</th>
    </thead>
    <tbody>
        @forelse($awarded as $application)
            <tr onclick="window.location='{{ route('training.skill.application.view', ['id' => $application->id]) }}';">
                <td class="application-status">
                    @if ($application->awarded_level == $application->applied_level)
                        <span class="fa fa-check success" title="Awarded"></span>
                    @elseif($application->awarded_level > 0)
                        <span class="fa fa-exclamation warning" title="Awarded a lower skill level"></span>
                    @else
                        <span class="fa fa-remove danger" title="Not awarded"></span>
                    @endif
                </td>
                <td class="application-skill dual-layer">
                    <div class="upper">{{ $application->skill->name }}</div>
                    <div class="lower">{{ $application->skill->category_name }}</div>
                </td>
                <td class="application-user dual-layer">
                    <div class="upper">{{ $application->user->name }}</div>
                    <div class="lower">{{ $application->user->username }}</div>
                </td>
                <td class="skill-level">
                    @include('training.skills.proficiency', ['level' => $application->applied_level])
                </td>
                <td class="skill-level">
                    @include('training.skills.proficiency', ['level' => $application->awarded_level])
                </td>
                <td class="awarded-user dual-layer">
                    <div class="upper">{{ $application->awarder->name }}</div>
                    <div class="lower">{{ $application->awarder->username }}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">There are no reviewed skill applications</td>
            </tr>
        @endforelse
    </tbody>
</table>
