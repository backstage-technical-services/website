@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'search-results')
@section('title', 'Search Results :: Resources')
@section('header-main', 'Resources')

@section('content')
    {!! Form::open(['route' => 'resource.search.process', 'id' => 'resource-search']) !!}
    @include('resources.forms.search')
    {!! Form::hidden('category', $search->category) !!}
    @foreach ($search->tags as $tag)
        {!! Form::hidden('tag[]', $tag) !!}
    @endforeach
    {!! Form::close() !!}

    @if ($resources->total())
        <div class="counts">Showing {{ $Counts['lower'] }} to {{ $Counts['upper'] }} of {{ $Counts['total'] }} results</div>
    @endif

    <div class="results-container">
        @forelse($resources as $resource)
            @include('resources.search.result')
        @empty
            <div class="empty">
                <h2>We couldn't find anything that matched your search.</h2>
                <p>
                    Try being less specific or browsing by category or tag from the
                    <a href="{{ route('resource.search') }}">homepage</a>.
                </p>
            </div>
        @endforelse
    </div>

    @if (get_class($resources) == 'Illuminate\Pagination\LengthAwarePaginator')
        {{ $resources }}
    @endif
@endsection
