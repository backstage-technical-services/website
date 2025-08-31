(function ($) {
    $.fn.ApplySelect2 = function (options) {
        this.each(function (i, element) {
            $(element).select2(
                jQuery.extend(
                    {
                        theme: 'bootstrap',
                        allowClear: false,
                    },
                    typeof options == 'object' ? options : {},
                ),
            );
        });
    };
})(jQuery);
