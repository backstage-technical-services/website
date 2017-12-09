(function ($) {
    /**
     * Allow any button to submit an AJAX request and process the response.
     * Data can be sent by using the 'data-' attributes.
     */
    $('body').on('click', '[data-submit-ajax]', function () {
        var btn = $(this);
        
        if(!btn.data('submitConfirm') || confirm(btn.data('submitConfirm'))) {
            var action = btn.data('submitAjax');
            var data = btn.data();
            var redirect = btn.data('successUrl') ? btn.data('successUrl') : window.location;
            delete data['submitAjax'];
            delete data['submitConfirm'];
            delete data['successUrl'];
            btn.attr('disabled', 'disabled');
            
            $.ajax({
                data   : $.param(data),
                url    : action,
                type   : "post",
                success: function () {
                    window.location = redirect;
                },
                error  : function (data) {
                    btn.attr('disabled', false);
                    var error = processAjaxErrors(data);
                    alert(error.message);
                }
            });
        }
    });
    
    /**
     *
     */
    function syncToggleHtml(element, value) {
        element.find('.fa')
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
            data    : $.param({
                action: 'update-field',
                field : element.data('field'),
                value : value ? 1 : 0
            }),
            url     : element.data('action'),
            type    : 'post',
            success : function () {
                element.trigger('toggle:success', [element, value]);
            },
            complete: function () {
                element.removeAttr('value-old');
            },
            error   : function (data) {
                var errors = processAjaxErrors(data).message;
                alert(typeof(errors) == 'object' ? [].concat.apply([], Object.values(errors)).join("\n") : errors);
                syncToggleHtml(element, !value);
            }
        });
    });
})(jQuery);