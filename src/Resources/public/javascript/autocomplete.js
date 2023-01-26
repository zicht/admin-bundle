/**
 */
var ZichtQuicklistAutocomplete = (function ($) {
    'use strict';

    function initTextControl($hidden, $text, service_url, callback) {
        callback = callback || $.noop;

        var language = $text.data('language') || false;
        var params = {};

        if ($text.data('language')) {
            params = {language: language};
        }

        var allowManualInput = $text.data('allow-manual-input') || false;
        var allowManualInputRegex = $text.data('allow-manual-input-regex') || '';

        $text
            .focus(function () {
                $text.select();
            })
            .autocomplete({
                appendTo: ".zicht-ui-autocompletecontainer_" + $text.attr('id'),
                source: function (req, resp) {
                    params.pattern = req.term;
                    $.get(
                        service_url,
                        params,
                        resp
                    );
                },
                select: function (e, ui) {
                    $hidden.val(ui.item.id);
                    $text.val(ui.item.label);
                    callback(ui.item);
                },
                change: function (e, ui) {
                    if (!allowManualInput) {
                        return;
                    }
                    var value = new String($(e.target).val());
                    if (allowManualInputRegex && !new RegExp(allowManualInputRegex, 'i').test(value)) {
                        return;
                    }

                    $hidden.val(value);
                }
            })
        ;
        return $text;
    }

    function initRemoveControl($control) {
        $control.on('click', function (e) {
            $control.parents('li:first').remove();
            e.preventDefault();
        });
    }

    /**
     * Initialize the pre-submit hook
     *
     * @param {jQuery} $hidden
     * @param {jQuery} $text
     * @param {jQuery} $form
     */
    function initPreSubmitCheck($hidden, $text, $form) {
        $form.on('submit', function () {
            if ($text.val() === '') {
                $hidden.val('');
            }
        });
    }

    function initSingleAutocomplete($hidden, service_url, callback) {
        var $text = $hidden.siblings('input[type="text"]');
        initTextControl($hidden, $text, service_url, callback);
        initPreSubmitCheck($hidden, $text, $hidden.closest('form'));
    }


    function initListItem($li, service_url, callback) {
        initRemoveControl($li.find('.remove-control'));
        initSingleAutocomplete($li.find('input[type="hidden"]'), service_url, callback);
    }


    function initMultipleAutocomplete($ul, service_url) {
        var $add = $ul.find('.add-control');
        var $items = $ul.find('>li');
        var $template = $ul.find('script#' + $ul.attr('data-template'));

        $items.each(function (i, li) {
            var $li = $(li);

            if ($li.find('.add-control')) {
                initListItem($li, service_url, function () {
                    addSelectionToList($(li));
                });
            } else {
                initListItem($li, service_url);
            }
        });

        function addSelectionToList($li) {
            var $added = $($template.text());

            // copy values and reset the 'add' control.
            $.each(['input[type="hidden"]', 'input[type="text"]'], function (i, ptn) {
                $added.find(ptn).val($li.find(ptn).val());
                $li.find(ptn).val('');
            });

            initListItem($added, service_url);
            $added.insertBefore($li);
            $li.find('input[type="text"]').focus();
        }


        $add.on('click', function (e) {
            e.preventDefault();
            var $li = $add.parents('li:first');
            // if no value set, ignore click
            if (!$li.find('input[type="hidden"]').val()) {
                return;
            }
            addSelectionToList($li);
        });
    }

    return {
        'init': function ($control, service_url, multiple, callback) {
            if (multiple) {
                initMultipleAutocomplete($control, service_url);
            } else {
                initSingleAutocomplete($control, service_url, callback);
            }
        }
    };
})(jQuery);
