(function($) {
    $.fn.EasyMDE = function() {
        this.each(function(i, element) {
            var easymde = new EasyMDE({
                element: element,
                status : false,
            });
        });
    };
})(jQuery);