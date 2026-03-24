/**
 * Koch Language System (i18n Engine)
 * Namespace: window.kochLang
 * localStorage key: koch_lang
 * Supports: th | en | zh | jp
 * 
 * Usage:
 *   Add data-i18n="section.key" on any element to translate its textContent.
 *   Add data-i18n-placeholder="section.key" to translate input placeholders.
 *   Call window.kochLang.setLang('en') to switch language programmatically.
 * 
 * To add new translatable text:
 *   1. Add a new key in all 4 JSON files (th.json, en.json, zh.json, jp.json)
 *   2. Add data-i18n="yourSection.yourKey" to the HTML element
 *   3. No JS changes needed.
 */

(function () {
    'use strict';

    // ── Guard: prevent double-init ──────────────────────────────────────────
    if (window.kochLang && window.kochLang._initialized) return;

    // ── Constants ───────────────────────────────────────────────────────────
    const STORAGE_KEY   = 'koch_lang';
    const SUPPORTED     = ['th', 'en', 'zh', 'jp'];
    const DEFAULT_LANG  = 'th';
    const FALLBACK_LANG = 'en';
    const LANG_BASE     = '../lang/';

    // ── In-memory cache ─────────────────────────────────────────────────────
    const _cache = {};

    let _currentLang = null;
    let _currentData = {};

    // ── Fetch + cache a language file ───────────────────────────────────────
    async function _loadLang(lang) {
        if (_cache[lang]) return _cache[lang];

        if (!SUPPORTED.includes(lang)) {
            console.warn('[kochLang] Unsupported language:', lang);
            return null;
        }

        try {
            const url = LANG_BASE + lang + '.json';
            const res = await fetch(url);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            _cache[lang] = data;
            return data;
        } catch (err) {
            console.error('[kochLang] Failed to load language file:', lang, err);
            return null;
        }
    }

    // ── Resolve a dot-notation key ───────────────────────────────────────────
    function _resolve(key, data) {
        if (!key || !data) return null;
        const parts = key.split('.');
        let val = data;
        for (const part of parts) {
            if (val == null || typeof val !== 'object') return null;
            val = val[part];
        }
        return (val !== undefined && val !== null && typeof val === 'string') ? val : null;
    }

    // ── XSS-safe text setter ────────────────────────────────────────────────
    function _safeSetText(el, text) {
        if (text === null || text === undefined) return;
        el.textContent = text;
    }

    // ── Apply translations to DOM ────────────────────────────────────────────
    function _applyTranslations(data, fallbackData) {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const text = _resolve(key, data) ?? _resolve(key, fallbackData) ?? null;
            if (text !== null) _safeSetText(el, text);
        });

        document.querySelectorAll('[data-i18n-html]').forEach(el => {
            const key = el.getAttribute('data-i18n-html');
            const text = _resolve(key, data) ?? _resolve(key, fallbackData) ?? null;
            if (text !== null) el.innerHTML = text;
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            const text = _resolve(key, data) ?? _resolve(key, fallbackData) ?? null;
            if (text !== null) el.setAttribute('placeholder', text);
        });

        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            const text = _resolve(key, data) ?? _resolve(key, fallbackData) ?? null;
            if (text !== null) el.setAttribute('title', text);
        });

        // data-i18n-value → input value attribute (for submit buttons)
        document.querySelectorAll('[data-i18n-value]').forEach(el => {
            const key = el.getAttribute('data-i18n-value');
            const text = _resolve(key, data) ?? _resolve(key, fallbackData) ?? null;
            if (text !== null) el.value = text;
        });
    }

    // ── Update <html lang=""> and language label ─────────────────────────────
    function _updateMeta(lang, data) {
        const htmlLangMap = { th: 'th', en: 'en', zh: 'zh-Hans', jp: 'ja' };
        document.documentElement.lang = htmlLangMap[lang] || lang;

        const fontMap = {
            th: "'Sarabun', 'Inter', sans-serif",
            en: "'Inter', 'Sarabun', sans-serif",
            zh: "'Noto Sans SC', 'Sarabun', 'Inter', sans-serif",
            jp: "'Noto Sans JP', 'Sarabun', 'Inter', sans-serif"
        };
        document.documentElement.style.setProperty('--koch-font-primary', fontMap[lang] || fontMap.th);

        const label = data && data.lang_label ? data.lang_label : lang.toUpperCase();
        const labelEl = document.getElementById('kochLangLabel');
        if (labelEl) labelEl.textContent = label;

        document.querySelectorAll('[data-koch-lang-btn]').forEach(btn => {
            const btnLang = btn.getAttribute('data-koch-lang-btn');
            btn.classList.toggle('koch-lang-btn--active', btnLang === lang);
        });
    }

    // ── Core: Set language ───────────────────────────────────────────────────
    async function setLang(lang) {
        if (!SUPPORTED.includes(lang)) {
            console.warn('[kochLang] Unknown language:', lang, '— falling back to', DEFAULT_LANG);
            lang = DEFAULT_LANG;
        }

        let data = await _loadLang(lang);

        let fallbackData = _currentData;
        if (!data) {
            console.warn('[kochLang] Falling back to', FALLBACK_LANG);
            data = await _loadLang(FALLBACK_LANG);
            if (!data) {
                console.error('[kochLang] Critical: could not load any language file.');
                return;
            }
            lang = FALLBACK_LANG;
        }

        if (lang !== FALLBACK_LANG && !_cache[FALLBACK_LANG]) {
            _loadLang(FALLBACK_LANG);
        }
        fallbackData = _cache[FALLBACK_LANG] || {};

        _currentLang = lang;
        _currentData = data;

        _applyTranslations(data, fallbackData);
        _updateMeta(lang, data);

        try {
            localStorage.setItem(STORAGE_KEY, lang);
        } catch (e) { /* ignore */ }
    }

    // ── Read saved language ──────────────────────────────────────────────────
    function _getSavedLang() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved && SUPPORTED.includes(saved)) return saved;
        } catch (e) { /* ignore */ }
        return DEFAULT_LANG;
    }

    // ── Initialize ───────────────────────────────────────────────────────────
    async function init() {
        const lang = _getSavedLang();
        await setLang(lang);
    }

    // ── Public API ───────────────────────────────────────────────────────────
    window.kochLang = {
        _initialized: true,
        setLang,
        getCurrentLang: () => _currentLang,
        getSupportedLangs: () => [...SUPPORTED],
        resolve: (key) => _resolve(key, _currentData)
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

})();
