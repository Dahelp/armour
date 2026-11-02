const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const source = fs.readFileSync('public/js/cookie-consent.js', 'utf8');
function scenario(value, blocked = false) {
    const scripts = []; let ready; let click;
    const notice = {hidden: true, querySelectorAll: () => [{addEventListener: (_, fn) => {click = fn;}}]};
    const store = {value};
    const window = {localStorage: {
        getItem: () => {if (blocked) throw Error('blocked'); return store.value;},
        setItem: (_, value) => {if (blocked) throw Error('blocked'); store.value = value;}
    }};
    const document = {referrer: '', head: {appendChild: s => scripts.push(s)},
        addEventListener: (_, fn) => {ready = fn;}, getElementById: () => notice,
        querySelector: () => ({content: '113728633'}), createElement: () => ({})};
    vm.runInNewContext(source, {window, document, location: {href: 'https://techtires.ru/'}});
    ready(); return {scripts, window, notice, store, click: () => click()};
}
for (const value of [null, '0', 'unexpected']) {
    const s = scenario(value);
    assert.equal(s.scripts.length, 0); assert.equal(s.window.ym, undefined); assert.equal(s.notice.hidden, false);
    s.click(); s.click(); assert.equal(s.scripts.length, 1); assert.equal(s.store.value, '1');
    assert.equal(s.window.ym.a.length, 1); assert.equal(s.notice.hidden, true);
}
const returning = scenario('1'); assert.equal(returning.scripts.length, 1); assert.equal(returning.notice.hidden, true);
const blocked = scenario(null, true); assert.equal(blocked.scripts.length, 0); blocked.click(); assert.equal(blocked.scripts.length, 1);
const layout = fs.readFileSync('app/views/armour/layouts/watches.php', 'utf8');
assert.ok(!layout.includes('mc.yandex.ru/watch/'));
const head = fs.readFileSync('app/views/armour/layouts/yandex-head.php', 'utf8');
assert.ok(!head.includes('<script')); assert.ok(!head.includes('mc.yandex.ru'));
console.log('Analytics consent scenarios passed.');
