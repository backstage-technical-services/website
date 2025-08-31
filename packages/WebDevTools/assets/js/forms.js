$('body').on('submit', 'form', function (event) {
    var form = $(event.target);
    form.append('<input type="hidden" name="TZ-OFFSET" value="' + new Date().getTimezoneOffset() + '">');
});
