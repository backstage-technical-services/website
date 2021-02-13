jQuery(document).ready(function() {
    $('main [data-type="simplemde"], main [data-type="easymde"]').EasyMDE();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function() {
            $modal.find('[data-type="simplemde"], [data-type="easymde"]').EasyMDE();
        });
    }
});