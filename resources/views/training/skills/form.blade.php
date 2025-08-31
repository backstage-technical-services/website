<fieldset>
    <legend>General Details</legend>
    <div class="form-group @InputClass('name')">
        {!! Form::label('name', 'Skill Name:', ['class' => 'control-label']) !!}
        {!! Form::text('name', null, ['class' => 'form-control']) !!}
        @InputError('name')
    </div>
    <div class="form-group @InputClass('category_id')">
        {!! Form::label('category_id', 'Category:', ['class' => 'control-label']) !!}
        {!! Form::select('category_id', $categories, null, ['class' => 'form-control']) !!}
        @InputError('category_id')
    </div>
    <div class="form-group @InputClass('description')">
        {!! Form::label('description', 'Description:', ['class' => 'control-label']) !!}
        {!! Form::textarea('description', null, [
            'class' => 'form-control',
            'rows' => 6,
            'placeholder' => 'Use this to describe what the skill is for and what it allows you to do',
        ]) !!}
        <p class="help-block alt">You can format this with markdown.</p>
        @InputError('description')
    </div>
</fieldset>

<fieldset id="level-requirements">
    <legend>Levels</legend>
    @if ($errors->has('levels'))
        <div class="form-group has-error">
            <p class="help-block">{{ $errors->first('levels') }}</p>
        </div>
    @endif

    {{-- Level 1 --}}
    <div class="form-group @InputClass('level1')">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('available[level1]', true, null, ['data-type' => 'toggle-visibility']) !!} {{ $LevelNames[1] }} is available
            </label>
        </div>
        {!! Form::textarea('level1', null, [
            'class' => 'form-control',
            'rows' => 4,
            'placeholder' =>
                'This is generally for the ability to perform the task while supervised by a member who is ' . $LevelNames[3],
            'data-visibility-input' => 'available[level1]',
            'data-visibility-state' => 'checked',
        ]) !!}
        @InputError('level1')
    </div>

    {{-- Level 2 --}}
    <div class="form-group @InputClass('level2')">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('available[level2]', true, null, ['data-type' => 'toggle-visibility']) !!} {{ $LevelNames[2] }} is available
            </label>
        </div>
        {!! Form::textarea('level2', null, [
            'class' => 'form-control',
            'rows' => 4,
            'placeholder' => 'This is generally for the ability to perform the task while unsupervised',
            'data-visibility-input' => 'available[level2]',
            'data-visibility-state' => 'checked',
        ]) !!}
        @InputError('level2')
    </div>

    {{-- Level 3 --}}
    <div class="form-group @InputClass('level3')">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('available[level3]', true, null, ['data-type' => 'toggle-visibility']) !!} {{ $LevelNames[3] }} is available
            </label>
        </div>
        {!! Form::textarea('level3', null, [
            'class' => 'form-control',
            'rows' => 4,
            'placeholder' => 'This is generally for the ability to teach, supervise and approve other members',
            'data-visibility-input' => 'available[level3]',
            'data-visibility-state' => 'checked',
        ]) !!}
        @InputError('level3')
    </div>
</fieldset>
