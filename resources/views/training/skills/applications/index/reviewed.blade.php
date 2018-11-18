<table class="table table-striped">
    <thead>
        <th class="proposal-status"></th>
        <th class="proposal-skill">Skill</th>
        <th class="proposal-user">User</th>
        <th class="skill-level">Applied for</th>
        <th class="skill-level">Awarded</th>
        <th class="awarded-user">Awarded by</th>
    </thead>
    <tbody>
        @forelse($awarded as $proposal)
            <tr onclick="window.location='{{ route('training.skill.proposal.view', ['id' => $proposal->id]) }}';">
                <td class="proposal-status">
                    @if($proposal->awarded_level == $proposal->proposed_level)
                        <span class="fa fa-check success" title="Awarded"></span>
                    @elseif($proposal->awarded_level > 0)
                        <span class="fa fa-exclamation warning" title="Awarded a lower skill level"></span>
                    @else
                        <span class="fa fa-remove danger" title="Not awarded"></span>
                    @endif
                </td>
                <td class="proposal-skill dual-layer">
                    <div class="upper">{{ $proposal->skill->name }}</div>
                    <div class="lower">{{ $proposal->skill->category_name }}</div>
                </td>
                <td class="proposal-user dual-layer">
                    <div class="upper">{{ $proposal->user->name }}</div>
                    <div class="lower">{{ $proposal->user->username }}</div>
                </td>
                <td class="skill-level">
                    @include('training.skills.proficiency', ['level' => $proposal->proposed_level])
                </td>
                <td class="skill-level">
                    @include('training.skills.proficiency', ['level' => $proposal->awarded_level])
                </td>
                <td class="awarded-user dual-layer">
                    <div class="upper">{{ $proposal->awarder->name }}</div>
                    <div class="lower">{{ $proposal->awarder->username }}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">There are no reviewed skill applications</td>
            </tr>
        @endforelse
    </tbody>
</table>