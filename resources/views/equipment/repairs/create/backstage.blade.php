{!! Form::model(new \App\Models\Equipment\Breakage(), ['route' => 'equipment.repairs.store', 'files' => true]) !!}
<p>
    If you discover a piece of equipment is broken please fill in the form form below, so that the Equipment Officer is
    informed of the breakage. Please also label the equipment and, if necessary, take it out of service.
</p>

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
        {!! Form::text('location', null, [
            'class' => 'form-control',
            'placeholder' => 'Where is the equipment currently?',
        ]) !!}
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
        {!! Form::textarea('description', null, [
            'class' => 'form-control',
            'placeholder' => 'What is wrong with it? Please be specific',
            'rows' => 5,
        ]) !!}
    </div>
    @InputError('description')
</div>

{{-- Images --}}
<div class="form-group @InputClass('images') @InputClass('images.*')">
    {!! Form::label('images', 'Images:', ['class' => 'control-label']) !!}
    <div class="input-group">
        <span class="input-group-addon"><span class="fa fa-camera"></span></span>
        {!! Form::file('images[]', ['accept' => '.png, .jpg, .jpeg', 'multiple']) !!}
    </div>
    <p class="help-block small">Maximum 5 images, 20MB each.</p>
    @InputError('images')
    @InputError('images.*')
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
