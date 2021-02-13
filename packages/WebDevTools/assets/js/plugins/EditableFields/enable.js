(function($) {
    /**
     * Allow any button to submit an AJAX request and process the response.
     * Data can be sent by using the 'data-' attributes.
     */
    $('body').on('click', '[data-submit-ajax]', function() {
        var btn = $(this);

        if (!btn.data('submitConfirm') || confirm(btn.data('submitConfirm'))) {
            var action = btn.data('submitAjax');
            var data   = btn.data();
            delete data['submitAjax'];
            delete data['submitConfirm'];
            delete data['redirect'];
            delete data['redirectLocation'];
            btn.attr('disabled', 'disabled');

            $.ajax({
                data   : $.param(data),
                url    : action,
                type   : 'post',
                success: function() {
                    if (btn.data('redirect') || btn.data('redirect') == 'true') {
                        if (btn.data('redirectLocation')) {
                            location.assign(btn.data('redirectLocation'));
                        } else {
                            location.reload(true);
                        }
                    }
                },
                error  : function(data) {
                    btn.attr('disabled', false);
                    var error = processAjaxErrors(data);
                    alert(error.message);
                },
            });
        }
    });
})(jQuery);