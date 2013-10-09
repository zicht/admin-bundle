(function() {
    if (typeof jQuery !== 'undefined') {
        var $ = jQuery;
        var logo_url = '/bundles/zichtadmin/images/zicht.png';

        $(function() {
            if ($('a', '#zicht_admin_menu').length > 0) {
                var $wrapper = $('<div id="zicht_admin_menu_wrapper" />');
                var $logo = $('<img class="logo" src="' + logo_url + '">');
                var $veil = $('<div id="zicht_admin_menu_veil" />');
                var $menu = $('<div />');

                $menu.hide().append($('#zicht_admin_menu'));
                $wrapper
                    .append($logo)
                    .append($menu);

                $(document.body)
                    .append($wrapper)
                    .append($veil)
                ;

                var proximity = false;
                var isExpanded = false;

                $logo.on('click', function() {
                    if (isExpanded) {
                        $menu.fadeOut('fast');
                        $veil.animate({opacity: 0}, function (){$veil.hide();});
                        $wrapper.removeClass('enabled');
                        isExpanded = false;
                    } else {
                        $veil.show();
                        $veil.animate({opacity: 0.6});
                        $menu.fadeIn('fast');
                        $wrapper.addClass('enabled');
                        isExpanded = true;
                    }
                });
                $veil.on('click', function() {
                    if (isExpanded) {
                        $veil.hide();
                    }
                });

                $(window).keyup(function(e) {
                    if (isExpanded && e.keyCode === 27) {
                        proximity = false;
                        isExpanded = false;
                        $menu.hide();
                        $wrapper.removeClass('enabled');
                        $veil.hide();
                    }
                });
            }
        });
    }
})();