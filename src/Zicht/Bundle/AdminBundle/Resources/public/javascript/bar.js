(function() {
    if (typeof jQuery !== 'undefined') {
        var $ = jQuery;
        var logo_url = '/bundles/zichtadmin/images/zicht.png';

        $(function() {
            if ($('a', '#zicht_admin_menu').length > 0) {
                $('html>head').append($('<link rel="stylesheet" type="text/css" href="/bundles/zichtadmin/style/bar.css">'));

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
                        $menu.hide();
                        isExpanded = false;
                        $wrapper.css('background-color', 'transparent');
                    } else {
                        $menu.slideDown();
                        $wrapper.css('left', '4px');
                        $wrapper.css('top', '4px');
                        $wrapper.css('background-color', 'white');
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
                        $wrapper.hide();
                        $veil.hide();
                    }
                });
                $(window).on('mousemove', function(e) {
                    if (!isExpanded) {
                        if (e.clientX < 100 && e.clientY < 100) {
                            var avg = (e.clientX + e.clientY) / 2;

                            $wrapper.css({
                                'left': (-40 + (50 - avg)) + 'px',
                                'top': (-38 + (50 - avg)) + 'px'
                            });
//                            $logo.css('opacity', .5 + (.5 - (avg / 2 / 50.0)));
                            $veil.css('opacity',.4 * (.5 + (.5 - (avg / 2 / 50.0))));

                            if (!proximity) {
                                proximity = true;
                                $wrapper.show();
                                $veil.show();
                            }
                        } else if (proximity) {
                            proximity = false;
                            $wrapper.hide();
                            $veil.hide();
                        }
                    }
                });
            }
        });
    }
})();