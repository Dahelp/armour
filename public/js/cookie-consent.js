(function () {
    'use strict';

    var storageKey = 'techtires_analytics_consent_v1';

    function getPreference() {
        try {
            return window.localStorage.getItem(storageKey);
        } catch (error) {
            return null;
        }
    }

    var analyticsStarted = false;

    function startAnalytics() {
        if (analyticsStarted) { return; }
        var counter = document.querySelector('meta[name="yandex-metrika-counter"]');
        if (!counter || !/^[1-9][0-9]*$/.test(counter.content)) { return; }
        analyticsStarted = true;
        window.ym = window.ym || function () {
            (window.ym.a = window.ym.a || []).push(arguments);
        };
        window.ym.l = Date.now();
        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://mc.yandex.ru/metrika/tag.js?id=' + counter.content;
        document.head.appendChild(script);
        window.ym(Number(counter.content), 'init', {
            ssr: true, webvisor: true, clickmap: true, ecommerce: 'dataLayer',
            referrer: document.referrer, url: location.href,
            accurateTrackBounce: true, trackLinks: true
        });
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

        if (getPreference() === '1') {
            startAnalytics();
        } else {
            notice.hidden = false;
        }

        notice.querySelectorAll('[data-cookie-acknowledge]').forEach(function (button) {
            button.addEventListener('click', function () {
                acknowledgeNotice();
                startAnalytics();
                notice.hidden = true;
            });
        });
    });
}());
