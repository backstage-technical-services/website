(function($) {
    $.fn.CheckboxAutoToggle = function() {
        $(this).on('change', 'input[data-type="toggle-all"][type="checkbox"]', function() {
            var checked = $(this).prop('checked');
            $(this).parents('table')
                   .find('tbody td.col--checkbox input[type="checkbox"]')
                   .prop('checked', checked);
        });
    };
})(jQuery);