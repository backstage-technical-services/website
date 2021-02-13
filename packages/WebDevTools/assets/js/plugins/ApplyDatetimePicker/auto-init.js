jQuery(document).ready(function() {
    $('main input[data-input-type="datetimepicker"]').ApplyDatetimePicker();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function() {
            $modal.find('input[data-input-type="datetimepicker"]').each(function(i, element) {
                element = $(element);
                element.ApplyDatetimePicker({
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
        });
    }
});