<table class="table table-striped">
    <thead>
        <th class="application-skill">Skill</th>
        <th class="application-user">User</th>
        <th class="application-level">Skill Level</th>
        <th class="application-date">Application Date</th>
    </thead>
    <tbody>
        @forelse($unawarded as $application)
            <tr onclick="window.location='{{ route('training.skill.application.view', ['id' => $application->id]) }}';">
                <td class="application-skill dual-layer">
                    <div class="upper">{{ $application->skill->name }}</div>
                    <div class="lower">{{ $application->skill->category_name }}</div>
                </td>
                <td class="application-user dual-layer">
                    <div class="upper">{{ $application->user->name }}</div>
                    <div class="lower">{{ $application->user->username }}</div>
                </td>
                <td class="application-level skill-level">
                    @include('training.skills.proficiency', ['level' => $application->proposed_level])
                </td>
                <td class="application-date dual-layer">
                    <div class="upper">{{ $application->date->format('jS M Y') }}</div>
                    <div class="lower">{{ $application->date->format('g:ia') }}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">There are no skill applications requiring review</td>
            </tr>
        @endforelse
    </tbody>
</table>