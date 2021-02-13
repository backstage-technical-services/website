(function($) {
    $.fn.SimpleMDE = function() {
        this.each(function(i, element) {
            var simplemde = new SimpleMDE({
                element: element,
                status : false,
            });
        });
    };
})(jQuery);