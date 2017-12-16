window.$modal         = new Modal('#modal');
$modal.errorProcessor = processAjaxErrors;

$modal.onShow(function() {
    // Reload datetimepicker widget
    $modal.find('input[data-input-type="datetimepicker"]').each(function(i, input) {
        var element = datetimepicker(input, {
            widgetParent: $('body'),
        });
        element.on('dp.show', function() {
            var widget = $('body > .bootstrap-datetimepicker-widget');
            widget.offset({
                left: element.offset().left,
            }).css('bottom', 'auto');
            if (widget.hasClass('top')) {
                widget.offset({
                    top: element.offset().top - widget.outerHeight(),
                });
            } else {
                widget.offset({
                    top: element.offset().top + element.outerHeight(),
                });
            }
        });
    });

    // Re-trigger any visibility toggles
    $('[data-type="toggle-visibility"]').trigger('change');

    // Apply select2
    $modal.find('select[select2]').each(function() {
        select2($(this), {
            dropdownParent: $modal.modal,
        });
    });
});