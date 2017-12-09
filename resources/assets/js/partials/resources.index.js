$('[data-type="filter-results"]').on('change', function () {
    var select = $(this);
    var base_url = select.data('baseUrl');
    var filter_name = select.data('filterName');
    var value = select.val();
    
    if(value) {
        window.location = base_url + '?filter=' + filter_name + ':' + value;
    } else {
        window.location = base_url;
    }
});