new Clipboard('[data-clipboard-target]');
$('body').on('click', '[data-clipboard-target]', function () {
    $.notification({
        level: 'info',
        message: 'Copied to clipboard',
    });
});
