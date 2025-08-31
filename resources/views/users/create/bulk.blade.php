<div class="form-group @if (Request::old('mode') == 'bulk') @InputClass('users') @endif">
    {!! Form::textarea('users', null, [
        'class' => 'form-control resize-y',
        'placeholder' => 'Fred Bloggs,fb123',
        'rows' => 6,
    ]) !!}
    @InputError('users')
</div>
