<div class="tab-vertical" id="training-skill-categories" role="tabpanel">
    <div class="tab-links">
        <ul class="nav nav-pills nav-stacked category-list" role="tablist">
            @foreach($SkillCategories as $i => $category)
                <li{{ $i == 0 ? ' class=active' : '' }}>
                    <a role="button">{{ $category->name }}</a>
                    <span class="label label-default">{{ $user->countSkills($category->id ? $category : null) }} / {{ count($category->skills) }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="tab-content">
        @foreach($SkillCategories as $i => $category)
            <div class="tab-pane{{ $i == 0 ? ' active' : '' }}">
                <table class="table table-striped user-skills">
                    <tbody>
                        @forelse($category->skills as $skill)
                            <tr>
                                <td class="skill-name">
                                    <a class="grey" href="{{ route('training.skill.view', ['id' => $skill->id]) }}">{{ $skill->name }}</a>
                                </td>
                                <td class="skill-proposal">
                                    @if($user->hasProposalPending($skill))
                                        <span class="fa fa-refresh success" title="Proposal pending"></span>
                                    @endif
                                </td>
                                <td class="skill-level">
                                    @if($user->hasSkill($skill))
                                        @include('training.skills.proficiency', ['level' => $user->getSkillLevel($skill)])
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3"><em>&ndash; there aren't any skills in this category &ndash;</em></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>