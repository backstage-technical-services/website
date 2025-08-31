/**
 * Attach events to the relevant modal elements when it's shown.
 */
$modal.onShow(function () {
    if ($modal.templateName == 'export') {
        $modal.form().on('change', '[type="checkbox"],[type="radio"]', function () {
            rebuildExportUrl();
        });
    } else if ($modal.templateName == 'select_date') {
        $modal.buttons().on('click', function () {
            var btn = $(this);
            var base_url = $modal.find('input[name="base_url"]').val();
            $modal.disableButtons();

            if (btn.is('[name="action"][value="select"]')) {
                window.location =
                    base_url +
                    '/' +
                    $modal.find('select[name="year"]').val() +
                    '/' +
                    $modal.find('select[name="month"]').val();
            } else {
                window.location = base_url;
            }
        });
    }
});

/**
 * Rebuild the Export Diary URL when the user changes
 * their selection of event tyes to show.
 */
function rebuildExportUrl() {
    var $BaseUrl = $modal.find('input[name="export_url"]').data('baseUrl');
    var $Query = {};

    // Build the list of event types to export
    var types = [];
    $modal.find('input[name="event_types"][type="checkbox"]').each(function () {
        if ($(this).is(':checked')) {
            types.push($(this).val());
        }
    });
    if (types.indexOf('events') == -1) {
        types.push('events');
    }
    if (types.length > 1) {
        $Query.types = types.join(',');
    }

    // Build the filter
    var crewing = $modal.find('input[name="crewing"][type="radio"]:checked').val();
    if (crewing != '*') {
        $Query.crewing = crewing;
    }

    // Build the query
    if ($.isEmptyObject($Query)) {
        $modal.find('input[name="export_url"]').val($BaseUrl);
    } else {
        $Query.user = $('input[name="member-id"]').val();
        $Query.token = $('input[name="member-event-token"]').val();
        $modal.find('input[name="export_url"]').val($BaseUrl + '?' + $.param($Query));
    }
}

/**
 * Show / hide the diary preferences dropdown
 */
$('#DiaryPreferences-edit').on('click', function () {
    $('#DiaryPreferences').toggle();
});
$('body').on('click', '*', function (event) {
    if (
        event.target.id != 'DiaryPreferences-edit' &&
        $(event.target).parents('#DiaryPreferences-edit').length == 0 &&
        $(event.target).parents('#DiaryPreferences').length == 0 &&
        $('#DiaryPreferences').is(':visible')
    ) {
        $('#DiaryPreferences').hide();
    }
});

/**
 * Rebuild the diary when the user changes their selection of events to show.
 */
$('#DiaryPreferences').on('change', 'input', function () {
    buildDiary();
});
function buildDiary() {
    // Get the event types
    var event_types = [];
    $('#DiaryPreferences')
        .find('[name^="event_types"]:checked')
        .each(function () {
            event_types.push(this.value);
        });

    // Get the crewing filter
    var crewing = $('#DiaryPreferences').find('[name="crewing"]:checked').val();

    // Get the events
    var events = $('.diary').find('div.event-list > div.event');
    events
        .hide()
        .filter('[data-event-type="' + event_types.join('"],[data-event-type="') + '"]')
        .filter(crewing == '*' ? '*' : '[data-crewing="' + crewing + '"]')
        .show();
}

/**
 * Save user preferences
 */
$('#DiaryPreferences-save').on('click', function () {
    var btn = $(this);
    var text = btn.html();

    btn.attr('disabled', true);
    btn.html('<span class="fa fa-spinner fa-spin"></span>');

    $.ajax({
        url: btn.data('updateUrl'),
        data: $(this.form).serialize(),
        type: 'post',
        error: function (data) {
            btn.removeAttr('disabled');
            btn.html(text);
            alert(processAjaxErrors(data).message);
        },
        success: function () {
            $('#DiaryPreferences').toggle();
            btn.removeAttr('disabled').html(text);
            $.notification({
                level: 'success',
                message: 'Preferences saved',
            });
        },
    });
});
