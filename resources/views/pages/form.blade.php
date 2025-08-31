<!-- Text field for 'title' -->
<div class="form-group @InputClass('title')">
    {!! Form::label('title', 'Page Title:', ['class' => 'control-label']) !!}
    {!! Form::text('title', null, ['class' => 'form-control']) !!}
    @InputError('title')
</div>

<!-- Text field for 'slug' -->
<div class="form-group @InputClass('slug')">
    {!! Form::label('slug', 'Page Slug:', ['class' => 'control-label']) !!}
    {!! Form::text('slug', null, ['class' => 'form-control']) !!}
    @InputError('slug')
</div>

<!-- Textarea for 'content' -->
<div class="form-group @InputClass('content')">
    {!! Form::label('content', 'Content:', ['class' => 'control-label']) !!}
    <div class="simplemde">
        {!! Form::textarea('content', null, ['class' => 'form-control', 'data-type' => 'simplemde']) !!}
    </div>
    @InputError('content')
    <p class="help-block alt">Use {!! link_to('https://simplemde.com/markdown-guide', 'markdown', ['target' => '_blank']) !!} to style the text.</p>
</div>

<!-- Select field for 'published' -->
<div class="form-group @InputClass('published')">
    {!! Form::label('published', 'Published:', ['class' => 'control-label']) !!}
    {!! Form::select('published', [0 => 'No', 1 => 'Yes'], null, ['class' => 'form-control', 'id' => 'published']) !!}
    @InputError('published')
</div>

<!-- Select field for 'user_id' -->
<div class="form-group @InputClass('user_id')">
    {!! Form::label('user_id', 'Author:', ['class' => 'control-label']) !!}
    {!! Form::memberList('user_id', null, ['class' => 'form-control', 'include_blank' => true]) !!}
    @InputError('user_id')
</div>
