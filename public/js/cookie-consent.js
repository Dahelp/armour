(function () {
    'use strict';

    var storageKey = 'techtires_cookie_preference';

    function getPreference() {
        try {
            return window.localStorage.getItem(storageKey);
        } catch (error) {
            return null;
        }
    }

    function savePreference(value) {
        try {
            window.localStorage.setItem(storageKey, value);
        } catch (error) {
            // The site remains usable when browser storage is unavailable.
        }

        window.dispatchEvent(new CustomEvent('techtires:cookie-preference', {detail: {preference: value}}));
    }

    document.addEventListener('DOMContentLoaded', function () {
        var notice = document.getElementById('cookie-consent');
        if (!notice) {
            return;
        }

        if (!getPreference()) {
            notice.hidden = false;
        }

        document.querySelectorAll('[data-cookie-settings]').forEach(function (button) {
            button.addEventListener('click', function () {
                notice.hidden = false;
                notice.querySelector('button[data-cookie-preference]')?.focus();
            });
        });

        notice.querySelectorAll('[data-cookie-preference]').forEach(function (button) {
            button.addEventListener('click', function () {
                savePreference(button.getAttribute('data-cookie-preference') || 'necessary');
                notice.hidden = true;
            });
        });
    });
}());
