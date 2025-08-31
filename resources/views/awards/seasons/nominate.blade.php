{!! Form::open(['route' => ['award.season.nomination.store', $season->id], 'class' => 'nominations']) !!}
<fieldset>
    <legend>Nominate for an award</legend>
    @include('awards.seasons.nominations.form')
    <div class="form-group">
        <div class="btn-group">
            <button class="btn btn-success" data-disable="click" data-disable-text="Nominating ...">
                <span class="fa fa-check"></span>
                <span>Nominate</span>
            </button>
        </div>

        <span class="form-link">
            or {!! link_to_route('award.season.index', 'Cancel') !!}
        </span>
    </div>
</fieldset>
{!! Form::close() !!}
