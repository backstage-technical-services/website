$('select[name="event_id"]').select2({
    ajax       : {
        dataType          : 'json',
        delay             : 250,
        type              : 'post',
        data              : function(params) {
            return {
                name: params.term,
            };
        },
        processResults    : function(data) {
            return {
                results: $.map(data.response, function(item) {
                    return {
                        id  : item.id,
                        text: item.name + ' (' + item.date + ')',
                    };
                }),
            };
        },
        cache             : true,
        minimumInputLength: 2,
        url               : $(this).data('ajaxUrl'),
    },
    placeholder: 'Start typing to search for an event',
    theme      : 'bootstrap',
});