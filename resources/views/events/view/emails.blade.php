<h2>Event Emails</h2>
@can('update', $event)
    <div class="top-buttons">
        <div class="btn-group">
            <button class="btn btn-success"
                    data-toggle="modal"
                    data-target="#modal"
                    data-modal-template="event_email"
                    data-mode="create"
                    type="button">
                <span class="fa fa-envelope"></span>
                <span>Email crew</span>
            </button>
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="mailto:{{ implode(',', $event->crew()->get()->map(function($crew) { return $crew->user->email; })->toArray()) }}?subject=[Backstage Website] {{ $event->name }}">
                        <span class="fa fa-external-link"></span>
                        <span>Use mail client</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
@endcan
<div class="email-wrapper">
    @forelse($event->emails as $email)
        <div class="email">
            <div class="subject">{{ $email->header }}</div>
            <div class="details">
                <div class="sender">
                    <div class="heading">From</div>
                    <div>{{ $email->sender->name }} ({{ $email->sender->username }})</div>
                </div>
                <div class="date">
                    <div class="heading">Sent</div>
                    <div>{{ $email->created_at->format('h:ia D M Y') }}</div>
                </div>
            </div>
            <div class="body">{!! Markdown::convertToHtml($email->body) !!}</div>
        </div>
    @empty
        <p>There are no crew emails for this event.</p>
    @endif
</div>