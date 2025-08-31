jQuery(document).ready(function () {
    $('main select[select2]').ApplySelect2();

    if (typeof $modal != 'undefined') {
        $modal.onShow(function () {
            $modal.find('select[select2]').ApplySelect2({
                dropdownParent: $modal.modal,
            });
        });
    }
});
