{!! Form::open(['route' => ['award.season.vote', $season->id], 'class' => 'voting']) !!}
<h3>Cast Your Vote</h3>
@include('awards.seasons.award_nominations')
<div>
    <button class="btn btn-success" data-disable="click" data-disable-text="Voting ...">
        <span class="fa fa-check"></span>
        <span>Vote for selected awards</span>
    </button>
    <span class="form-link">
        or {!! link_to_route('award.season.index', 'Cancel') !!}
    </span>
</div>
{!! Form::close() !!}