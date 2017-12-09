/**
 * Use pushState for the tabs
 */
if (history.pushState) {
    $('ul.nav li').on('click', function () {
        var url = $(this).children('a').attr('href');
        if (window.location.href != url) {
            history.pushState({}, '', url);
        }
    });
}

/**
 * Attach events to the relevant modal elements when it's shown.
 */
$modal.onShow(function () {
    if ($modal.mode == 'edit') {
        var select = $modal.form().find('[name="user_id"]');
        var member = select.find('option:selected').text();
        $('<p class="form-control-static">' + member + '</p>').insertAfter(select);
        select.remove();
    }
});

/**
 * Attach to toggle event for crew list.
 */
$('body').on('toggle:success', 'div.crew-list [data-editable="toggle"]', function (event, element, value) {
    var parent = element.parent();
    var form_data = parent.data('formData');
    form_data['confirmed'] = value;
    parent.data('formData', form_data);
});

/**
 * Attach to toggle event for paperwork.
 * This updates the paperwork count and shows/hides relevant elements.
 */
$('body').on('toggle:success', 'div.paperwork-list [data-editable="toggle"]', function (event, element, value) {
    // Show / hide any links
    var parent = element.parent();
    parent.find('[data-show]').addClass('hidden');
    if (value) {
        parent.find('[data-show="complete"]').removeClass('hidden');
    } else {
        parent.find('[data-show="incomplete"]').removeClass('hidden');
    }

    // Update the count
    var badge = $('ul.nav').find('[title="Event Resources"]').find('span.badge');
    var count = $('div.paperwork-list').find('.fa-remove').length;

    if (count == 0) {
        badge.remove();
    } else {
        if (badge.length < 1) {
            badge = $('<span class="badge"></span>');
            $('ul.nav').find('[title="Event Resources"]').append(badge);
        }
        badge.text(count);
    }
});