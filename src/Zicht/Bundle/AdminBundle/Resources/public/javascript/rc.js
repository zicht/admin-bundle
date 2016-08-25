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
    var doRequest, setState;

    /**
     * Send an XHR and call callback after the response was received.
     *
     * @param {String} method
     * @param {String} url
     * @param {function} callback
     */
    doRequest = function (method, url, callback) {
        var xhr = new XMLHttpRequest();

        xhr.open(method, url, true);
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
    };


    /**
     * Set element state
     *
     * @param {HTMLElement} el
     * @param {String} state
     * @param {String} text
     */
    setState = function (el, state, text) {
        switch (state) {
            case 'error':
                el.setAttribute('class', 'glyphicon glyphicon-remove');
                el.style.color = 'red';

                break;
            case 'ok':
                el.setAttribute('class', 'glyphicon glyphicon-ok');
                el.style.color = 'green';

                break;
            case 'on':
                el.setAttribute('class', 'glyphicon glyphicon-ok');
                el.style.color = 'green';

                break;
            case 'off':
                el.setAttribute('className', 'glyphicon');

                break;
        }
        el.setAttribute('title', text);
    };


    handleErrorResponse = function (el, response, responseTxt) {
        var isError = false;

        if (!response) {
            setState(el, 'error', responseTxt);
            isError = true;
        } else if (typeof response.error !== 'undefined') {
            setState(el, 'error', response.error);
            isError = true;
        }
        return isError;
    };

    /**
     * Registers a form as being an RC form
     *
     * @param {HTMLFormElement} form
     */
    function registerForm(form) {
        var button = form.getElementsByTagName('button').item(0),
            state = form.ownerDocument.createElement('i');

        state.style.paddingLeft = '14px';
        button.parentNode.insertBefore(state, button.nextSibling);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            return false;
        });

        console.log(':)');

        if (form.getAttribute('data-mode') === 'toggle') {
            doRequest(
                'GET',
                form.getAttribute('action'),
                function (response, responseTxt) {
                    var toggleStatus,
                        updateStatus = function(response) {
                            toggleStatus = response;
                            if (toggleStatus.status) {
                                setState(state, 'ok');
                            } else {
                                setState(state, 'off');
                            }
                        };

                    if (handleErrorResponse(state, response, responseTxt)) {
                        return;
                    }
                    updateStatus(response);

                    button.addEventListener('click', function (e) {
                        button.setAttribute('disabled', 'disabled');

                        state.setAttribute('class', 'glyphicon glyphicon-hourglass');
                        state.style.color = 'grey';

                        doRequest(
                            toggleStatus.status ? 'DELETE' : 'POST',
                            form.getAttribute('action'),
                            function (response, responseTxt) {
                                if (handleErrorResponse(state, response, responseTxt)) {
                                    return;
                                }

                                updateStatus(response);
                            }
                        );
                        button.removeAttribute('disabled');
                    });
                }
            );
        } else {
            button.addEventListener('click', function(e) {
                button.setAttribute('disabled', 'disabled');

                state.setAttribute('class', 'glyphicon glyphicon-hourglass');
                state.style.color = 'grey';

                e.preventDefault();

                doRequest(
                    form.getAttribute('method'),
                    form.getAttribute('action'),
                    function(response, responseTxt) {
                        if (handleErrorResponse(state, response, responseTxt)) {
                            return;
                        }

                        setState(state, 'ok', typeof response.message !== 'undefined' ? response.message : '');

                        button.removeAttribute('disabled');
                    }
                );

                return false;
            });
        }
    }


    return function(element) {
        var forms = element.getElementsByTagName('form'),
            i;

        for (i = 0; i < forms.length; i ++) {
            registerForm(forms.item(i));
        }
    };
})();
