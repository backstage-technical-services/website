$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'TZ-OFFSET': (new Date).getTimezoneOffset(),
    },
    method: 'GET',
    dataType: 'json',
});

function processAjaxErrors(data) {
    var error = {
        code: 500,
        key: '',
        message: 'Oops, an unknown error has occurred',
        isList: false,
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
            error.message = response_error;
            error.isList = true;
        }
    }

    return error;
}