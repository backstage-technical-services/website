<div class="award-list">
    @foreach ($season->awards()->get() as $award)
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ $award->name }}
                <p class="description">{{ $award->description }}</p>
            </div>
            @if ($award->hasApprovedNominations($season->id))
                <?php $nominations = $award->getApprovedNominations($season->id); ?>
                <table class="table">
                    <tbody>
                        @foreach ($nominations as $nomination)
                            <tr>
                                @if ($season->isVotingOpen())
                                    <td class="vote">
                                        @if (!$award->userHasVoted($season, Auth::user()))
                                            {!! Form::radio('awards[' . $award->id . ']', $nomination->id) !!}
                                        @elseif($nomination->userVotedFor(Auth::user()))
                                            <span class="fa fa-check success"
                                                title="You voted for this nomination"></span>
                                        @endif
                                    </td>
                                @elseif($season->areResultsReleased())
                                    <td class="result">
                                        @if ($nomination->hasWon())
                                            <span class="fa fa-check success" title="This nomination won"></span>
                                        @elseif(Auth::user()->can('update', $season))
                                            <span class="fa fa-remove danger"></span>
                                        @endif
                                        @can('update', $season)
                                            <div class="vote-count" title="{{ $nomination->votes()->count() }} votes">
                                                ({{ $nomination->votes()->count() }})
                                            </div>
                                        @endcan
                                    </td>
                                @endif
                                <td class="details">
                                    <div class="nominee">{{ $nomination->nominee }}</div>
                                    <div class="reason">{!! nl2br($nomination->reason) !!}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="panel-body">
                    <p>There {{ $season->isVotingOpen() ? 'aren\'t' : 'weren\'t' }} any nominations for this award.</p>
                </div>
            @endif
        </div>
    @endforeach
</div>
