jQuery(document).ready(function() {
    $('main table').CheckboxAutoToggle();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function() {
            $modal.find('table').CheckboxAutoToggle();
        });
    }
});