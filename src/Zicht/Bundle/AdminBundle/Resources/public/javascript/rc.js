/* global: zicht_admin_rc */
/**
 * zicht_admin_rc is a simple front end helper to do "rc" request (RC stands for Remote Control) to the back end.
 * This is typically suitable for flushing caches, triggering reindexes, etc. etc.
 *
 * This JS is included by the controls.html.twig template and runs stand-alone (vanilla), but it makes use of the
 * `glyphicons` class names to show a status indicator next to the button.
 *
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
var zicht_admin_rc = (function() {

    /**
     * Send the form as an XHR and call callback after the response was received.
     *
     * @param {HTMLFormElement} form
     * @param {function} callback
     */
    function send(form, callback) {
        var xhr = new XMLHttpRequest();

        xhr.open(form.getAttribute('method'), form.getAttribute('action'), true);
        xhr.onreadystatechange = function() {
            var data = null;

            if (xhr.readyState === 4) {
                data = false;

                if (xhr.getResponseHeader('Content-Type').indexOf('application/json') >= 0) {
                    data = JSON.parse(xhr.responseText);
                }

                callback(data, xhr.responseText);
            }
        };
        xhr.send();
    }


    /**
     * Registers a form as being an RC form
     *
     * @param {HTMLFormElement} form
     */
    function registerForm(form) {
        var button = form.getElementsByTagName('button').item(0),
            state = form.ownerDocument.createElement('i');

        state.setAttribute('className', 'glyphicon');
        state.style.paddingLeft = '14px';
        button.parentNode.insertBefore(state, button.nextSibling);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            return false;
        });
        button.addEventListener('click', function(e) {
            button.setAttribute('disabled', 'disabled');

            state.setAttribute('class', 'glyphicon glyphicon-hourglass');
            state.style.color = 'grey';

            e.preventDefault();

            send(form, function(response, responseTxt) {
                if (!response) {
                    state.setAttribute('class', 'glyphicon glyphicon-remove');
                    state.style.color = 'red';

                    state.setAttribute('title', responseTxt);
                } else if (typeof response.error !== 'undefined') {
                    state.setAttribute('class', 'glyphicon glyphicon-remove');
                    state.style.color = 'red';

                    state.setAttribute('title', response.error);
                } else {
                    state.setAttribute('class', 'glyphicon glyphicon-ok');
                    if (typeof response.message !== 'undefined') {
                        state.setAttribute('title', response.message);
                    } else {
                        state.setAttribute('title', '');
                    }
                    state.style.color = 'green';
                }
                button.removeAttribute('disabled');
            });

            return false;
        });
    }


    return function(element) {
        var forms = element.getElementsByTagName('form'),
            i;

        for (i = 0; i < forms.length; i ++) {
            registerForm(forms.item(i));
        }
    };
})();
