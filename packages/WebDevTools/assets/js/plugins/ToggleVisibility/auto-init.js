jQuery(document).ready(function() {
    $('main').ToggleVisibility();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function() {
            $modal.modal.ToggleVisibility();
        });
    }
});