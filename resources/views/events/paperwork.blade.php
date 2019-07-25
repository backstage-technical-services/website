@extends('app.main')

@section('page-section', 'events')
@section('page-id', 'paperwork')
@section('header-main', 'Paperwork')
@section('title', 'Paperwork List')

@section('content')
    <div class="top-buttons">
        <button class="btn btn-success"
                data-toggle="modal"
                data-target="#modal"
                data-modal-template="paperwork"
                data-modal-title="Add Paperwork"
                data-modal-class="modal-sm"
                data-form-action="{{ route('event.paperwork.store') }}"
                data-mode="create"
                type="button">
            <span class="fa fa-plus"></span>
            <span>Add Paperwork</span>
        </button>
    </div>

    <table class="table table-striped">
        <thead>
            <th col="event">Paperwork</th>
            <th col="venue">Template</th>
            <th class="admin-tools"></th>
        </thead>
        <tbody>
            @forelse($paperwork_list as $paperwork)
                <tr>
                    <td col="name">{{ $paperwork->name }}</td>
                    <td col="em">
                        @if($paperwork->template_link)
                            <a href={{$paperwork->template_link}} > {{ $paperwork->name}} Form </a>
                        @else
                            <em>- none -</em>
                        @endif
                    </td>
                    <td class="admin-tools">
                        <button class="btn btn-warning btn-sm"
                                data-toggle="modal"
                                data-target="#modal"
                                data-modal-template="paperwork"
                                data-modal-title="Edit Paperwork"
                                data-modal-class="modal-sm"
                                data-form-action="{{ route('event.paperwork.update', ['id' => $paperwork->id]) }}"
                                data-form-data="{{ json_encode($paperwork) }}"
                                data-mode="edit"
                                data-editable="false" >
                            <span class="fa fa-pencil"></span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">There is no paperwork</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection

@section('modal')
    @include('events.modals.paperwork')
@endsection