(function ($) {
    var search_input = $('.search-tools .search input[type="text"]');
    
    // Add the placeholder
    search_input.attr('placeholder', 'Search ...');
    
    // Listen for the submit
    search_input.on('keyup', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code == 13) {
            var base_url = search_input.data('baseUrl');
            var base_query = search_input.data('baseQuery');
            e.preventDefault();
            window.location = base_url + '?' + $.param($.extend({}, base_query, {search: search_input.val()}));
        }
    });
})(jQuery);