(function ($) {
    $.fn.OtherInput = function () {
        this.each(function (i, element) {
            var input = $(element);
            var other_input = $('[name="' + input.data('otherInput') + '"]');
            var other_value = input.data('otherValue') || 'other';

            if (other_input.length) {
                other_input.addClass('input-other');
                input.on('change', function () {
                    if (input.val() == other_value) {
                        other_input.show();
                    } else {
                        other_input.hide();
                    }
                });

                other_input.css('display', input.val() == other_value ? 'block' : 'none');
            }
        });
    };
})(jQuery);
