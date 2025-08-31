(function ($) {
    $.fn.ToggleVisibility = function () {
        $(this).on('change', '[data-type="toggle-visibility"]', function () {
            var input = $(this);
            var name = input.attr('name');
            var type = input.attr('type');
            var form = $(this.form);

            form.find('[data-visibility-input="' + name + '"]').hide();

            if (type == 'checkbox') {
                var state = this.checked ? 'checked' : 'unchecked';
                form.find('[data-visibility-input="' + name + '"][data-visibility-state="' + state + '"]').show();
            } else if (type == 'radio') {
                if (input.is(':checked')) {
                    form.find(
                        '[data-visibility-input="' + name + '"][data-visibility-value*="' + this.value + '"]',
                    ).show();
                }
            } else {
                form.find('[data-visibility-input="' + name + '"][data-visibility-value*="' + this.value + '"]').show();
            }
        });

        $(this).find('[data-type="toggle-visibility"]').trigger('change');
    };
})(jQuery);
