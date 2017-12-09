{!! Form::open(['id' => 'DiaryPreferences']) !!}
<h2>Event Types:</h2>
<div class="form-group">
    <div class="checkbox">
        <label>
            {!! Form::checkbox('event_types[]', 'event', Auth::user()->isDiaryPreference('event_types', 'event')) !!} Events
        </label>
    </div>
    <div class="checkbox">
        <label>
            {!! Form::checkbox('event_types[]', 'training', Auth::user()->isDiaryPreference('event_types', 'training')) !!} Training Sessions
        </label>
    </div>
    <div class="checkbox">
        <label>
            {!! Form::checkbox('event_types[]', 'social', Auth::user()->isDiaryPreference('event_types', 'social')) !!} Socials
        </label>
    </div>
    <div class="checkbox">
        <label>
            {!! Form::checkbox('event_types[]', 'meeting', Auth::user()->isDiaryPreference('event_types', 'meeting')) !!} Meetings
        </label>
    </div>
    <div class="checkbox">
        <label>
            {!! Form::checkbox('event_types[]', 'hidden', Auth::user()->isDiaryPreference('event_types', 'hidden')) !!} Hidden / General Info
        </label>
    </div>
</div>
<h2>Show:</h2>
<div class="form-group">
    <div class="radio">
        <label>
            {!! Form::radio('crewing', '*', Auth::user()->isDiaryPreference('crewing', '*')) !!} All events
        </label>
    </div>
    <div class="radio">
        <label>
            {!! Form::radio('crewing', 'true', Auth::user()->isDiaryPreference('crewing', 'true')) !!} Events I'm crewing
        </label>
    </div>
</div>
<button class="btn btn-success"
        data-update-url="{{ route('member.update') }}"
        id="DiaryPreferences-save">
    <span>Save Preferences</span>
</button>
{!! Form::input('hidden', 'update', 'diary-preferences') !!}
{!! Form::close() !!}