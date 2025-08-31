<h3 class="mobile-only">{{ $category->name }}</h3>
<table class="table table-striped">
    <tbody>
        @forelse($category->skills as $skill)
            <tr>
                <td col="skill-name">
                    {!! link_to_route('training.skill.view', $skill->name, ['id' => $skill->id], ['class' => 'grey']) !!}
                </td>
                <td col="skill-buttons">
                    <div class="btn-group btn-group-sm">
                        @can('edit', $skill)
                            <a class="btn btn-warning btn-sm" href="{{ route('training.skill.edit', ['id' => $skill->id]) }}">
                                <span class="fa fa-pencil"></span>
                            </a>
                        @endcan
                        @can('delete', $skill)
                            <button
                                class="btn btn-danger btn-sm"
                                data-submit-ajax="{{ route('training.skill.destroy', $skill->id) }}"
                                data-submit-confirm="Are you sure you want to delete this skill?"
                                data-redirect="true"
                                data-redirect-location="{{ request()->url() }}"
                                type="button"
                            >
                                <span class="fa fa-remove"></span>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td class="em" colspan="2">&ndash; there aren't any skills in this category &ndash;</td>
            </tr>
        @endforelse
    </tbody>
</table>
