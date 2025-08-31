<div data-type="modal-template" data-id="export">
    <div class="modal-header">
        <h1>Export Events Diary</h1>
    </div>
    <div class="modal-body">
        {!! Form::open(['class' => 'export']) !!}
        <div class="input-group">
            <input
                class="form-control export-url"
                id="export_url"
                data-base-url="{{ route('event.export') }}"
                name="export_url"
                value="{{ route('event.export') }}"
                readonly
            >
            <span class="input-group-btn">
                <button
                    class="btn btn-default"
                    data-clipboard-target="#export_url"
                    title="Copy to clipboard"
                    type="button"
                >
                    <span class="fa fa-clipboard"></span>
                </button>
            </span>
        </div>
        @if (Auth::check() && Auth::user()->hasExportToken())
            <div class="customise-export">
                <div>
                    Show:
                </div>
                <div class="inputs">
                    <div class="checkbox">
                        <label class="checkbox-inline disabled">
                            <input
                                name="event_types"
                                type="checkbox"
                                value="event"
                                checked
                                disabled
                            > Events
                        </label>
                        <label class="checkbox-inline">
                            <input name="event_types" type="checkbox" value="training"> Training
                        </label>
                        <label class="checkbox-inline">
                            <input name="event_types" type="checkbox" value="social"> Socials
                        </label>
                        <label class="checkbox-inline">
                            <input name="event_types" type="checkbox" value="meeting"> Meetings
                        </label>
                    </div>
                    <div class="radio">
                        <label class="radio-inline">
                            <input
                                name="crewing"
                                type="radio"
                                value="*"
                                checked
                            >Show all events
                        </label>
                        <label class="radio-inline">
                            <input name="crewing" type="radio" value="true">Only show events I'm crewing
                        </label>
                    </div>
                </div>
            </div>
        @else
            <p class="help-block">To customise which events are exported, please enable this in {!! link_to_route('member.profile', 'your profile') !!}.
            </p>
        @endif
        <h2>Importing to Google Calendar:</h2>
        <ol>
            <li>Open <a href="http://calendar.google.com/" target="_blank">Google Calendar</a></li>
            <li>Go to the <strong>Other Calendars</strong> menu on the left-hand side and click the down arrow</li>
            <li>Choose <strong>Add by URL</strong></li>
            <li>Enter the URL above into the pop-up box</li>
            <li>Click <strong>Add Calendar</strong></li>
        </ol>
        <p class="help-block">
            <strong>Please note:</strong> The frequency of calendar updates is determined by Google and cannot be
            configured.
        </p>
        {!! Form::close() !!}
    </div>
    <div class="modal-footer">
        <button
            class="btn btn-success"
            data-toggle="modal"
            data-target="#modal"
            type="button"
        >
            <span class="fa fa-thumbs-up"></span>
            <span>Ok, got it</span>
        </button>
    </div>
</div>
