(function () {
    'use strict';

    var storageKey = 'techtires_cookie_notice_acknowledged';

    function getPreference() {
        try {
            return window.localStorage.getItem(storageKey);
        } catch (error) {
            return null;
        }
    }

    function acknowledgeNotice() {
        try {
            window.localStorage.setItem(storageKey, '1');
        } catch (error) {
            // The site remains usable when browser storage is unavailable.
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var notice = document.getElementById('cookie-consent');
        if (!notice) {
            return;
        }

        if (!getPreference()) {
            notice.hidden = false;
        }

        notice.querySelectorAll('[data-cookie-acknowledge]').forEach(function (button) {
            button.addEventListener('click', function () {
                acknowledgeNotice();
                notice.hidden = true;
            });
        });
    });
}());
