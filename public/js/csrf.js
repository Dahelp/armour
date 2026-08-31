(function () {
    'use strict';

    var meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;
    var token = meta.getAttribute('content') || '';
    if (!token) return;

    function protectForm(form) {
        if ((form.method || 'get').toLowerCase() !== 'post' || form.querySelector('input[name="_csrf"]')) return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_csrf';
        input.value = token;
        form.appendChild(input);
    }

    document.querySelectorAll('form').forEach(protectForm);
    document.addEventListener('submit', function (event) {
        protectForm(event.target);
    }, true);

    var originalOpen = XMLHttpRequest.prototype.open;
    var originalSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url) {
        this._csrfMethod = String(method || 'GET').toUpperCase();
        this._csrfUrl = url;
        return originalOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function () {
        if (['POST', 'PUT', 'PATCH', 'DELETE'].indexOf(this._csrfMethod) !== -1) {
            var target = new URL(this._csrfUrl, window.location.href);
            if (target.origin === window.location.origin) this.setRequestHeader('X-CSRF-Token', token);
        }
        return originalSend.apply(this, arguments);
    };

    if (window.fetch) {
        var originalFetch = window.fetch;
        window.fetch = function (resource, options) {
            options = options || {};
            var method = String(options.method || 'GET').toUpperCase();
            var target = new URL(typeof resource === 'string' ? resource : resource.url, window.location.href);
            if (target.origin === window.location.origin && ['POST', 'PUT', 'PATCH', 'DELETE'].indexOf(method) !== -1) {
                options.headers = new Headers(options.headers || {});
                options.headers.set('X-CSRF-Token', token);
            }
            return originalFetch.call(this, resource, options);
        };
    }
}());
