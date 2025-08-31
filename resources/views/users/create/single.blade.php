<div class="form-group @if (Request::old('mode') == 'single') @InputClass('name') @endif">
    {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Both their forename and surname']) !!}
    @InputError('name')
</div>
<div class="form-group @if (Request::old('mode') == 'single') @InputClass('username') @endif">
    {!! Form::label('username', 'Username:', ['class' => 'control-label']) !!}
    <div class="input-group">
        {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'ab123']) !!}
        <span class="input-group-addon">@bath.ac.uk</span>
    </div>
    @InputError('username')
</div>
