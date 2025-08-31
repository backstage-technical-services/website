@extends('app.main')

@section('page-section', 'resources')
@section('page-id', 'history')
@section('title', 'Resource History :: Resources')

@section('content')
    <div class="resource-header">
        <h2>{{ $resource->title }}</h2>
        <h4>{{ $resource->category_name }}</h4>
    </div>
    <h3>Document History</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th col="num">#</th>
                <th col="date">Date</th>
                <th col="author">Author</th>
                <th col="reason">Reason for Issue</th>
                <th class="admin-tools"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resource->issues as $issue)
                <tr>
                    <td col="num">{{ $issue->issue }}</td>
                    <td col="date">{{ $issue->created_at->format('jS F Y') }}</td>
                    <td col="author">{{ $issue->author->name }}</td>
                    <td col="reason">{!! Markdown::convertToHtml($issue->reason) !!}</td>
                    <td class="admin-tools">
                        <a class="btn btn-primary"
                            href="{{ route('resource.stream', ['id' => $resource->id, 'issue' => $issue->issue]) }}"
                        >
                            View
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="back">
        <hr>
        <p>
            <a href="{{ route('resource.view', ['id' => $resource->id]) }}">Back</a>
        </p>
    </div>
@endsection
