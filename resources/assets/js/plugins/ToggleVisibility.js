(function ($) {
    $('body').on('change', '[data-type="toggle-visibility"]', function () {
        var input = $(this);
        var name = input.attr('name');
        var type = input.attr('type');
        var form = $(this.form);
        
        form.find('[data-visibility-input="' + name + '"]').addClass('hidden');
        
        if(type == 'checkbox') {
            if(this.checked) {
                var state = 'checked';
            } else {
                var state = 'unchecked';
            }
            form.find('[data-visibility-input="' + name + '"][data-visibility-state="' + state + '"]').removeClass('hidden');
        } else {
            form.find('[data-visibility-input="' + name + '"][data-visibility-value="' + input.val() + '"]').removeClass('hidden');
        }
    });
    $('[data-type="toggle-visibility"]').trigger('change');
})(jQuery);