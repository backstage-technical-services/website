@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'search-index')
@section('title', 'Resources')
@section('header-main', 'Resources')

@section('content')
    {!! Form::open(['route' => 'resource.search.process', 'id' => 'resource-search']) !!}
    @include('resources.forms.search')
    <div class="category-tag-summary">
        <fieldset>
            <legend>Categories</legend>
            @if(count($ResourceCategories) > 0)
                <div class="summary-wrapper">
                    <ul>
                        @foreach($ResourceCategories as $category)
                            <li>{!! link_to_route('resource.search', $category->name, ['category' => $category->slug], ['class' => 'grey']) !!}
                                ({{ $category->resources()->accessible()->count() }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                No categories.
            @endif
        </fieldset>
        <fieldset>
            <legend>Tags</legend>
            @if(count($ResourceTags) > 0)
                <div class="summary-wrapper">
                    <ul class="tag-list">
                        @foreach($ResourceTags as $tag)
                            <li>@include('resources.tags.partial')</li>
                        @endforeach
                    </ul>
                </div>
            @else
                No tags.
            @endif
        </fieldset>
    </div>
    {!! Form::close() !!}
@endsection