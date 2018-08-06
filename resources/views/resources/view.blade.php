@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'view')
@section('title', $resource->title . ' :: Resources')

@section('content')
    <div class="resource-header">
        <h2>{{ $resource->title }}</h2>
        <h4>{{ $resource->category_name }}</h4>
    </div>
    <iframe src="{{ route('resource.stream', ['id' => $resource->id]) }}"
            width="100%"
            height="600"
            frameborder="0"
            marginheight="0"
            marginwidth="0">Loading...
    </iframe>
    <div class="details-wrapper">
        <div class="links">
            <div class="btn-group">
                @if($resource->isFile())
                    <a class="btn btn-success" href="{{ route('resource.download', ['id' => $resource->id]) }}">
                        <span class="fa fa-cloud-download"></span>
                        <span>Download</span>
                    </a>
                @elseif($resource->isGDoc())
                    <a class="btn btn-success" href="{{ $resource->getFilePath() }}" target="_blank">
                        <span class="fa fa-external-link"></span>
                        <span>Open</span>
                    </a>
                @endif
                @can('update', $resource)
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle"
                                data-toggle="dropdown"
                                type="button">
                            <span class="fa fa-pencil"></span>
                            <span>Edit</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a href="{{ route('resource.edit', ['id' => $resource->id]) }}">
                                    <span class="fa fa-pencil"></span> Edit information
                                </a>
                            </li>
                            @if($resource->isFile())
                                <li>
                                    <a href="{{ route('resource.issue', ['id' => $resource->id]) }}">
                                        <span class="fa fa-upload"></span> Issue new version
                                    </a>
                                </li>
                            @endif
                        </ul>

                    </div>
                @endcan
            </div>
        </div>
        <div class="details">
            @if($resource->isIssuable())
                <p>
                    Issue {{ $resource->issue }} added by {{ $resource->author->name }} {{ $resource->issue()->created_at->diffForHumans() }} |
                    <a href="{{ route('resource.history', ['id' => $resource->id]) }}">View history</a>
                </p>
            @else
                <p>
                    Added by {{ $resource->author->name }} {{ $resource->created_at->diffForHumans() }} |
                    Last updated {{ $resource->updated_at->diffForHumans() }}
                </p>
            @endif
            @if($resource->isAttachedToEvent())
                <p>Related event: <a class="grey"
                                     href="{{ route('event.view', ['id' => $resource->event->id]) }}"
                                     target="_blank">{{ $resource->event->name }}</a></p>
            @endif
        </div>
    </div>
    <div class="clearfix"></div>
    @if($resource->description)
        <div class="description">
            {!! Markdown::convertToHtml($resource->description) !!}
        </div>
    @endif
    @if($resource->tags()->count() > 0)
        <ul class="tag-list">
            @foreach($resource->tags()->get() as $tag)
                <li>@include('resources.tags.partial')</li>
            @endforeach
        </ul>
    @endif
@endsection