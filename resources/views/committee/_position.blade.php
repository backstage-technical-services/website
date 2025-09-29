@if($role)
    <div class="position">
        @can('update', $role)
            <a class="btn btn-warning btn-sm edit-button"
               data-toggle="modal"
               data-target="#modal"
               data-modal-template="committee_add"
               data-modal-title="Edit Committee Role"
               data-modal-class="modal-sm"
               data-save-action="{{ route('committee.edit') }}"
               data-mode="edit"
               data-form-data="{{ json_encode($role) }}"
               title="Edit this role">
                <span class="fa fa-pencil"></span>
            </a>
        @endcan
        <div class="left">
            <div class="picture">
                <img class="img-rounded" src="{{ $role->user ? $role->user->getAvatarUrl() : '/images/profiles/blank.jpg' }}">
            </div>
            <div class="email">
                <a class="grey" href="mailto:{{ $role->email }}" title="Email them">
                    <span class="fa fa-envelope"></span>
                    <span class="hidden-xs">{{ $role->email }}</span>
                </a>
            </div>
        </div>
        <div class="right">
            <div class="title">
                {{ $role->name }}
            </div>
            <div class="name {{ $role->user !== null ? '' : 'em' }}">
                {{ $role->user !== null ? $role->user->name : 'Unelected' }}
            </div>
            <div class="description">
                {!!
                Markdown::convertToHtml(str_replace('[name]', $role->user ? $role->user->forename : ('The ' . strtolower($role->name)), $role->description))
                !!}
            </div>
        </div>
    </div>
@endif