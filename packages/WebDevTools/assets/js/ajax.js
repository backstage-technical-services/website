var $xhttp2 = typeof(window.FormData) != 'undefined';

jQuery.ajaxSetup({
    headers : {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'TZ-OFFSET'   : (new Date).getTimezoneOffset(),
    },
    method  : 'GET',
    dataType: 'json',
});

function processAjaxErrors(data) {
    var error = {
        code   : 500,
        key    : '',
        message: 'Oops, an unknown error has occurred',
        isList : false,
    };

    // Check for a detailed error message
    if (typeof(data.responseJSON) == 'object') {
        var response_error = data.responseJSON;

        // Status
        error.code = data.status;

        // Error code
        if (typeof(response_error.error_code) != 'undefined') {
            error.key = response_error.error_code;
        }

        // Error message
        if (typeof(response_error.__error) != 'undefined') {
            error.message = response_error.error;
        } else {
            error.message = response_error.errors;
            error.isList  = true;
        }
    }

    return error;
}

function renderFormAjaxErrors($form, $errors) {
    if ($errors.isList) {
        $form.find('input,textarea,select').each(function() {
            var input = $(this);
            if (typeof input.attr('name') != 'undefined') {
                var name = input.attr('name').replace(/(\[\])/gi, '');
                if (name in $errors.message) {
                    input.addClass('is-invalid');
                    $('<div/>')
                        .addClass('invalid-feedback')
                        .attr('data-type', 'ajax-error')
                        .text($errors.message[name][0])
                        .insertAfter(input.parent().hasClass('input-group') ? input.parent() : input);
                } else {
                    input.addClass('is-valid');
                }
            }
        });
    } else {
        $form.prepend($('<div />').addClass('alert alert-warning')
                                  .attr('data-type', 'ajax-error')
                                  .prepend('<span class="fa fa-exclamation"></span>').append('<span>' + $errors.message + '</span>'));
    }
}