jQuery(document).ready(function () {
    $('main [data-disable="click"]').DisableButtonOnClick();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function () {
            $modal.find('[data-disable="click"]').DisableButtonOnClick();
        });
    }
});
