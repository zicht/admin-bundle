/* global: zicht_admin_rc */
/**
 * zicht_admin_rc is a simple front end helper to do "rc" request (RC stands for Remote Control) to the back end.
 * This is typically suitable for flushing caches, triggering reindexes, etc. etc.
 *
 * This JS is included by the controls.html.twig template and runs stand-alone (vanilla), but it makes use of the
 * `glyphicons` class names to show a status indicator next to the button.
 *
 * @copyright Zicht online <http://zicht.nl>
 */
var zicht_admin_rc = (function() {
    var doRequest, setState, handleErrorResponse;

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
                el.setAttribute('class', 'glyphicon glyphicon-ban-circle');
                el.style.color = 'red';

                break;
            case 'ok':
                el.setAttribute('class', 'glyphicon glyphicon-ok-circle');
                el.style.color = 'green';

                break;
            case 'on':
                el.setAttribute('class', 'glyphicon glyphicon-ok-circle');
                el.style.color = 'green';

                break;
            case 'off':
                el.setAttribute('class', 'glyphicon glyphicon-ban-circle');
                el.style.color = 'orange';

                break;
            case 'loading':
                el.setAttribute('class', 'glyphicon glyphicon-play-circle');
                el.style.color = 'grey';

                break;
            case 'hide':
                el.setAttribute('class', 'glyphicon');

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

        state.style.paddingRight = '4px';
        button.insertBefore(state, button.firstChild);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            return false;
        });

        if (form.getAttribute('data-mode') === 'toggle') {
            doRequest(
                'GET',
                form.getAttribute('action'),
                function (response, responseTxt) {
                    var toggleStatus,
                        updateStatus = function(response) {
                            toggleStatus = response;
                            if (toggleStatus.status) {
                                setState(state, 'on');
                            } else {
                                setState(state, 'off');
                            }
                            button.removeAttribute('disabled');
                        };

                    if (handleErrorResponse(state, response, responseTxt)) {
                        return;
                    }
                    updateStatus(response);

                    button.addEventListener('click', function (e) {
                        setState(state, 'loading');
                        button.setAttribute('disabled', 'disabled');

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
                    });
                }
            );
        } else {
            setState(state, 'loading');

            button.addEventListener('click', function(e) {
                button.setAttribute('disabled', 'disabled');

                setState(state, 'loading');

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
