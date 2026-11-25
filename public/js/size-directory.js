(() => {
    'use strict';
    const form = document.querySelector('[data-size-directory]');
    if (!form) return;
    const input = form.querySelector('[name="q"]');
    const stock = form.querySelector('[name="stock"]');
    const clear = form.querySelector('[data-size-clear]');
    const items = [...document.querySelectorAll('[data-size-key]')];
    const count = document.querySelector('[data-size-count]');
    const empty = document.querySelector('[data-size-empty]');
    const key = value => value.trim().toLowerCase().replace(/[*х×]/g, 'x').replace(/,/g, '.').replace(/\s+/g, '');
    function filter() {
        if (input.matches(':focus') && composing) return;
        const query = key(input.value);
        clear.hidden = input.value.length === 0;
        let total = 0;
        items.forEach(item => {
            const visible = item.dataset.sizeKey.includes(query) && (!stock.checked || Number(item.dataset.sizeStock) > 0);
            item.hidden = !visible;
            if (visible) total++;
        });
        count.textContent = String(total);
        empty.hidden = total > 0;
    }
    let composing = false;
    input.addEventListener('compositionstart', () => { composing = true; });
    input.addEventListener('compositionend', () => { composing = false; filter(); });
    input.addEventListener('input', filter);
    stock.addEventListener('change', filter);
    clear.addEventListener('click', () => { input.value = ''; composing = false; filter(); input.focus(); });
    filter();
    // Standard GET submission keeps committed searches shareable and works without JS.
})();
