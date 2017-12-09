(function ($) {
    $.fn.DisableButtons = function () {
        this.each(function () {
            console.log('This plugin is deprecated. Use [data-disable="click"] instead.');
            var btn = $(this);
            btn.on('click', function (e) {
                btn.addClass("disabled")
                    .off("click").on("click", function () {
                    return false;
                })
                    .children('span.fa').attr("class", '').addClass('fa fa-refresh fa-spin').next().text(btn.attr('disable-submit') || "Working ...");
            });
        });
    };
    
    jQuery(document).ready(function () {
        $('button[disable-submit]').DisableButtons();
    });
})(jQuery);
(function ($) {
    $('body').on('click', 'button[data-disable="click"]', function (e) {
        var btn = $(this);
        var text = btn.attr('data-disable-text') || null;
        var icon = btn.attr('data-disable-icon') || 'spinner';
        var html = '<span class="fa fa-' + icon + ' fa-spin"></span>';
        if(text) {
            html += '<span>' + text + '</span>';
        }
        
        setTimeout(function() {
            btn.attr('disabled', true)
                .off('click')
                .html(html);
        }, 0);
    });
})(jQuery);