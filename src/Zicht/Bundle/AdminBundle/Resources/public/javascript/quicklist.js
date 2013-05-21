(function($){
    $('input[type="text"][data-quicklist-url]').each(function(i, el) {
        var $el = $(el);
        var url = $el.attr('data-quicklist-url');
        $el
            .focus(
                function(){
                    $el.select();
                }
            )
            .autocomplete({
                source: function(req, resp) {
                    $.get(
                        url,
                        {'pattern': req.term},
                        resp
                    )
                },
                select: function(e, ui) {
                    window.location.href = ui.item.url;
                }
            });
    });
})(jQuery);
