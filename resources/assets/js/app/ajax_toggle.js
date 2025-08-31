(function ($) {
    /**
     *
     */
    function syncToggleHtml(element, value) {
        element
            .find('.fa')
            .removeClass('fa-check')
            .removeClass('fa-remove')
            .addClass(value ? 'fa-check' : 'fa-remove');
    }

    $('body').on('click', '[data-editable="toggle"]', function (event) {
        event.stopPropagation();

        // Get the variables
        var element = $(this);
        var value = element.find('.fa').hasClass('fa-check');

        // Store the value in case of an error and toggle
        element.attr('value-old', value);
        value = !value;

        // Change the HTML
        syncToggleHtml(element, value);

        // Send the request
        $.ajax({
            data: $.param({
                action: 'update-field',
                field: element.data('field'),
                value: value ? 1 : 0,
            }),
            url: element.data('action'),
            type: 'post',
            success: function () {
                element.trigger('toggle:success', [element, value]);
            },
            complete: function () {
                element.removeAttr('value-old');
            },
            error: function (data) {
                var errors = processAjaxErrors(data).message;
                alert(typeof errors == 'object' ? [].concat.apply([], Object.values(errors)).join('\n') : errors);
                syncToggleHtml(element, !value);
            },
        });
    });
})(jQuery);
