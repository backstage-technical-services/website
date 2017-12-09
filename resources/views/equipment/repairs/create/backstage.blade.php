{!! Form::model(new \App\Models\Equipment\Breakage(), ['route' => 'equipment.repairs.store']) !!}
<p>If you discover a piece of equipment is broken please fill in the form form below, so that the E&S officer is informed of the breakage. Please also label the
    equipment and take it to the Drama Store.</p>

{{-- Equipment name --}}
<div class="form-group @InputClass('name')">
    {!! Form::label('name', 'Equipment Name:', ['class' => 'control-label']) !!}
    <div class="input-group">
        <span class="input-group-addon"><span class="fa fa-pencil"></span></span>
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'What\'s broken?']) !!}
    </div>
    @InputError('name')
</div>

{{-- Equipment location --}}
<div class="form-group @InputClass('location')">
    {!! Form::label('location', 'Equipment Location:', ['class' => 'control-label']) !!}
    <div class="input-group">
        <span class="input-group-addon"><span class="fa fa-map-marker"></span></span>
        {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => 'Where is the equipment currently?']) !!}
    </div>
    @InputError('location')
</div>

{{-- Marked --}}
<div class="form-group @InputClass('label')">
    {!! Form::label('label', 'Labelling:', ['class' => 'control-label']) !!}
    <div class="input-group">
        <span class="input-group-addon"><span class="fa fa-tag"></span></span>
        {!! Form::text('label', null, ['class' => 'form-control', 'placeholder' => 'How is the item marked as broken?']) !!}
    </div>
    @InputError('label')
</div>

{{-- Damage description --}}
<div class="form-group @InputClass('description')">
    {!! Form::label('description', 'Damage Description:', ['class' => 'control-label']) !!}
    <div class="input-group textarea">
        <span class="input-group-addon"><span class="fa fa-quote-left"></span></span>
        {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => 'What is wrong with it? Please be specific','rows' => 5]) !!}
    </div>
    @InputError('description')
</div>

{{-- Buttons --}}
<div class="form-group">
    <button class="btn btn-success" disable-submit="Reporting breakage">
        <span class="fa fa-check"></span>
        <span>Add breakage</span>
    </button>
    <span class="form-link">
        or {!! link_to_route('equipment.repairs.index', 'Cancel') !!}
    </span>
</div>
{!! Form::close() !!}