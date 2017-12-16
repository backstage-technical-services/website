{{-- Name --}}
<div class="form-group @InputClass('title')">
    {!! Form::label('title', 'Title:', ['class' => 'control-label']) !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'What is it called?']) !!}
    @InputError('title')
</div>
{{-- Description --}}
<div class="form-group @InputClass('description')">
    {!! Form::label('description', 'Description:', ['class' => 'control-label']) !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Briefly describe the resource']) !!}
    @InputError('description')
    <p class="help-block alt">This supports {!! link_to('https://simplemde.com/markdown-guide', 'markdown', ['target' => '_blank']) !!}.</p>
</div>
{{-- Source --}}
@if($mode == 'create')
    <div class="resource-source @InputClass('file')">
        <div class="form-group">
            {!! Form::label('source', 'Source:', ['class' => 'control-label']) !!}
            {{-- Type --}}
            {!! Form::select('type', $ResourceTypes, null, ['class' => 'form-control', 'data-type' => 'toggle-visibility']) !!}
            {{-- File --}}
            <div class="form-group source-input @InputClass('file')" data-visibility-input="type" data-visibility-value="{{ \App\Models\Resources\Resource::TYPE_FILE }}">
                {!! Form::file('file') !!}
                @InputError('file')
            </div>
        </div>
    </div>
@endif
{{-- Category / Tags --}}
<div class="form-group">
    <div class="row">
        {{-- Category --}}
        <div class="col-md-6">
            <div class="form-group @InputClass('category_id')">
                {!! Form::label('category_id', 'Category:', ['class' => 'control-label']) !!}
                {!! Form::select('category_id', ['' => '-- Uncategorised --'] + $ResourceCategories->pluck('name', 'id')->all(), null, ['class' => 'form-control']) !!}
                @InputError('category_id')
            </div>
        </div>
        {{-- Tags --}}
        <div class="col-md-6">
            <div class="form-group @InputClass('tags')">
                {!! Form::label('tags[]', 'Tags:', ['class' => 'control-label']) !!}
                {!! Form::select('tags[]', $ResourceTags->pluck('name', 'id')->all(), null, ['class' => 'form-control', 'multiple' => 'multiple', 'select2' => 'Use this to group similar documents']) !!}
                @InputError('tags')
            </div>
        </div>
    </div>
</div>
{{-- Access --}}
<div class="form-group @InputClass('access')">
    {!! Form::label('access', 'Access:', ['class' => 'control-label']) !!}
    {!! Form::select('access', $AccessLevels, null, ['class' => 'form-control']) !!}
    @InputError('access')
    <p class="help-block alt">Select who should be able to view this resource</p>
</div>
{{-- Event --}}
<div class="form-group @InputClass('event_id')">
    {!! Form::label('event_id', 'Link to event:', ['class' => 'control-label']) !!}
    <select class="form-control"
            data-ajax--url="{{ route('event.search') }}"
            name="event_id">
        @if($mode == 'edit' && $resource->isAttachedToEvent())
            <option value="{{ $resource->event->id }}" selected>{{ $resource->event->name }} ({{ $resource->event->end->format('M Y') }})</option>
        @endif
    </select>
    @InputError('event_id')
    <p class="help-block alt">If you link the resource to an event, it'll appear when you view the event.</p>
</div>