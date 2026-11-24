(() => {
    'use strict';
    const form = document.querySelector('[data-home-selection]');
    if (!form) return;
    const input = form.querySelector('input[name="s"]');
    const clear = form.querySelector('[data-selection-clear]');
    const error = form.querySelector('[data-selection-error]');
    const options = [...form.querySelectorAll('option[data-url]')];
    const key = value => value.trim().toLowerCase().replace(/[*х×]/g, 'x').replace(/,/g, '.').replace(/\s+/g, '');
    form.noValidate = true;
    const update = () => {
        clear.hidden = input.value.length === 0;
        error.hidden = true;
        input.removeAttribute('aria-invalid');
    };
    input.addEventListener('input', update);
    clear.addEventListener('click', () => { input.value = ''; update(); input.focus(); });
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && event.isComposing) event.preventDefault();
    });
    form.addEventListener('submit', event => {
        const query = input.value.trim();
        if (!query) {
            event.preventDefault(); error.hidden = false; input.setAttribute('aria-invalid', 'true'); input.focus(); return;
        }
        input.value = query;
        const matches = options.filter(option => key(option.value) === key(query));
        if (matches.length !== 1) return;
        const url = matches[0].dataset.url;
        if (url && url.startsWith('/') && !url.startsWith('//')) {
            event.preventDefault(); window.location.assign(url);
        }
    });
    update();
})();
