jQuery(function ($) {
    'use strict';

    function supports_html5_storage() {
        try {
            return 'localStorage' in window && window['localStorage'] !== null;
        } catch (e) {
            return false;
        }
    }

    function store_tab($selectedTab) {
        var data  = {
            uri: window.location.pathname,
            index : $navTabs.find('li').index($selectedTab)
        };

        // Store tab index
        localStorage.setItem("zicht_opened_tab", JSON.stringify(data));
    }

    if (supports_html5_storage()) {
        var $navTabs = $('ul.nav-tabs'),
            openedTab = 0,
            $firstTab = null
            ;

        if (openedTab = localStorage.getItem("zicht_opened_tab")) {
            openedTab = JSON.parse(openedTab);

            if (window.location.pathname == openedTab.uri) {
                if ($navTabs.find('li:eq(' + openedTab.index + ')').find('a').length) {
                    // Trigger click on tab stored in local storage
                    $navTabs.find('li:eq(' + openedTab.index + ')').find('a').trigger('click');
                }
            }
        }

        $('[name^=btn_update_and_edit]').click(function (e) {
            e.preventDefault();

            // Get active tab
            $firstTab = $navTabs.find('li.active');

            // Store tab index
            localStorage.setItem("zicht_opened_tab", $navTabs.find('li').index($firstTab));

            // Submit closest form
            $(this).closest('form').submit();
        });

        $navTabs.find('li').find('a').click(function (e) {
            store_tab($(this).parent());
        });
    }
});