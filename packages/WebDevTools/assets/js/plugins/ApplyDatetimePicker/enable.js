(function ($) {
    var Defaults = {
        allowInputToggle: true,
        format: 'YYYY-MM-DD',
        icons: {
            time: 'far fa-clock',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fas fa-bullseye',
            clear: 'far fa-trash',
            close: 'far fa-times',
        },
        locale: moment().locale('en-gb'),
        showTodayButton: true,
        useCurrent: true,
    };

    $.fn.ApplyDatetimePicker = function (options) {
        this.each(function (i, element) {
            var attributes = {};
            var regex = /^data\-date\-(.*)/;
            var regex_result;
            $.each(element.attributes, function (i, attribute) {
                regex_result = regex.exec(attribute.name);
                if (regex_result != null) {
                    attributes[regex_result[1].toCamelCase()] = attribute.value;
                }
            });

            options = $.extend({}, Defaults, typeof options == 'object' ? options : {}, attributes);

            $(element).datetimepicker(options);
        });
    };
})(jQuery);
