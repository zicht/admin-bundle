jQuery(function ($) {
    'use strict';

    function supports_html5_storage() {
        try {
            return 'localStorage' in window && window['localStorage'] !== null;
        } catch (e) {
            return false;
        }
    }

    if (supports_html5_storage()) {
        var $navTabs = $('ul.nav-tabs'),
            openedTab = 0,
            $firstTab = null
            ;

        if (openedTab = localStorage.getItem("zicht_opened_tab")) {
            // Trigger click on tab stored in local storage
            $navTabs.find('li:eq(' + openedTab + ')').find('a').trigger('click');

            // Remove value so it does not interfere when editing new pages etc..
            localStorage.removeItem('zicht_opened_tab');
        }


        $('input[name^=btn_update_and_edit]').click(function (e) {
            e.preventDefault();

            // Get active tab
            $firstTab = $navTabs.find('li.active');

            // Store tab index
            localStorage.setItem("zicht_opened_tab", $navTabs.find('li').index($firstTab));

            // Submit closest form
            $(this).closest('form').submit();
        });
    }
});