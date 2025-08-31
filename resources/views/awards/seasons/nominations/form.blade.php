<div class="form-group @InputClass('award_id')">
    {!! Form::label('award_id', 'Award:', ['class' => 'control-label']) !!}
    {!! Form::select('award_id', ['' => '-- Select --'] + $season->awards()->pluck('name', 'id')->toArray(), null, [
        'class' => 'form-control',
    ]) !!}
    @InputError('award_id')
</div>
<div class="form-group @InputClass('nominee')">
    {!! Form::label('nominee', 'Nominee:', ['class' => 'control-label']) !!}
    {!! Form::text('nominee', null, ['class' => 'form-control', 'placeholder' => 'Who or what?']) !!}
    @InputError('nominee')
</div>
<div class="form-group @InputClass('reason')">
    {!! Form::label('reason', 'Reason:', ['class' => 'control-label']) !!}
    {!! Form::textarea('reason', null, [
        'class' => 'form-control',
        'placeholder' => 'Why should this nominee win?',
        'rows' => 6,
    ]) !!}
    @InputError('reason')
</div>
