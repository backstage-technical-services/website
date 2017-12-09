<table class="table table-striped">
    <thead>
        <th class="proposal-skill">Skill</th>
        <th class="proposal-user">User</th>
        <th class="proposal-level">Proposed Level</th>
        <th class="proposal-date">Proposed</th>
    </thead>
    <tbody>
        @forelse($unawarded as $proposal)
            <tr onclick="window.location='{{ route('training.skill.proposal.view', ['id' => $proposal->id]) }}';">
                <td class="proposal-skill dual-layer">
                    <div class="upper">{{ $proposal->skill->name }}</div>
                    <div class="lower">{{ $proposal->skill->category_name }}</div>
                </td>
                <td class="proposal-user dual-layer">
                    <div class="upper">{{ $proposal->user->name }}</div>
                    <div class="lower">{{ $proposal->user->username }}</div>
                </td>
                <td class="proposal-level skill-level">
                    @include('training.skills.proficiency', ['level' => $proposal->proposed_level])
                </td>
                <td class="proposal-date dual-layer">
                    <div class="upper">{{ $proposal->date->format('jS M Y') }}</div>
                    <div class="lower">{{ $proposal->date->format('g:ia') }}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">There are no skill proposals requiring review</td>
            </tr>
        @endforelse
    </tbody>
</table>