/* global: window, document */
(function() {
    'use strict';
    var loader = function() {
        var logo_url = '/bundles/zichtadmin/images/fabrique.png',
            container = document.getElementById('zicht_admin_menu'),
            links = container.getElementsByTagName('a'),
            wrapper = document.createElement('div'),
            logo = document.createElement('img'),
            veil = document.createElement('div'),
            menu = document.createElement('div'),
            proximity = false,
            isExpanded = false,
            delegate = ('attachEvent' in document ? document.attachEvent : document.addEventListener)
            ;

        wrapper.style.display = 'none';

        logo.setAttribute('src', logo_url);
        logo.classList.add('logo');

        if (links.length) {
            wrapper.setAttribute('id', 'zicht_admin_menu_wrapper');
            veil.setAttribute('id', 'zicht_admin_menu_veil');

            veil.style.transition = 'all 0.3s ease-in-out';
            veil.style.MozTansition = 'all 0.3s ease-in-out';
            veil.style.webkitTransition = 'all 0.3s ease-in-out';

            veil.style.display = 'none';
            menu.style.display = 'none';

            menu.appendChild(container);

            wrapper.appendChild(logo);
            wrapper.appendChild(menu);

            document.body.appendChild(wrapper);
            document.body.appendChild(veil);

            delegate.call(document, 'click', function (e) {
                if (e.target == logo) {
                    if (isExpanded) {
                        menu.style.display = 'none';
                        wrapper.className = '';
                        if ('addEventListener' in veil) {
                            veil.addEventListener("transitionend", function() {
                                veil.style.display = 'none';
                            }, false);
                        }
                        veil.style.opacity = 0;
                        isExpanded = false;
                    } else {
                        wrapper.className = 'enabled';
                        menu.style.display = 'block';
                        veil.style.display = 'block';
                        veil.style.opacity = 0.6;
                        isExpanded = true;
                    }
                } else if (e.target == veil) {
                    if (isExpanded) {
                        proximity = false;
                        isExpanded = false;
                        wrapper.className = '';
                        menu.style.display = 'none';
                        veil.style.display = 'none';
                    }
                }
            });

            wrapper.style.display = 'block';
            container.style.display = 'block';

            window.addEventListener('keyup', function(e) {
                if (isExpanded && e.keyCode === 27) {
                    proximity = false;
                    isExpanded = false;
                    wrapper.className = '';
                    menu.style.display = 'none';
                    veil.style.display = 'none';
                }
            });
        }
    };

    if (document.getElementById('zicht_admin_menu')) {
        loader();
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            loader();
        });
    }
})();
