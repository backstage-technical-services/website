jQuery(document).ready(function () {
    $('main [data-type="simplemde"]').SimpleMDE();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function () {
            $modal.find('[data-type="simplemde"]').SimpleMDE();
        });
    }
});
