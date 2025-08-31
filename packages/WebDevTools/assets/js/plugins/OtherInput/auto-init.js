jQuery(document).ready(function () {
    $('main [data-other-input]').OtherInput();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function () {
            $modal.find('[data-other-input]').OtherInput();
        });
    }
});
