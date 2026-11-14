<script>
    const np = (typeof NProgress !== 'undefined') ? NProgress : { start: () => {}, done: () => {}, configure: () => {} };
    np.configure({ showSpinner: false, speed: 400, minimum: 0.1 });

    document.addEventListener('click', function (e) {
        let target = e.target.closest('a');
        if (target && target.href && !target.href.startsWith('javascript:') && !target.href.includes('#') && target.target !== '_blank') {
            np.start();
        }
    });

    // ─── Universal Button Lock & Anti-Double-Submit System ───
    function lockElement(el) {
        if (!el || el.dataset.locked === 'true') return;
        el.dataset.locked = 'true';
        el.dataset.prevPointerEvents = el.style.pointerEvents || '';
        el.dataset.prevOpacity = el.style.opacity || '';
        el.dataset.prevCursor = el.style.cursor || '';
        
        el.style.pointerEvents = 'none';
        el.style.opacity = '0.6';
        el.style.cursor = 'wait';
        if (el.tagName === 'BUTTON' || el.tagName === 'INPUT') {
            el.disabled = true;
        }
    }

    function unlockElement(el) {
        if (!el || el.dataset.locked !== 'true') return;
        el.dataset.locked = 'false';
        el.style.pointerEvents = el.dataset.prevPointerEvents || '';
        el.style.opacity = el.dataset.prevOpacity || '';
        el.style.cursor = el.dataset.prevCursor || '';
        if (el.tagName === 'BUTTON' || el.tagName === 'INPUT') {
            el.disabled = false;
        }
    }

    function unlockAll() {
        document.querySelectorAll('[data-locked="true"]').forEach(unlockElement);
        document.querySelectorAll('form[data-submitting="true"]').forEach(f => f.dataset.submitting = 'false');
        np.done();
    }

    let lastActionEl = null;
    let lastActionTime = 0;

    document.addEventListener('click', function (e) {
        let link = e.target.closest('a');
        if (link && link.href && !link.href.startsWith('javascript:') && !link.href.includes('#') && link.target !== '_blank') {
            np.start();
        }

        const actionEl = e.target.closest('button, [type="submit"], a.btn, a[data-action]');
        if (!actionEl) return;

        const now = Date.now();
        if (actionEl === lastActionEl && (now - lastActionTime) < 400) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }

        lastActionEl = actionEl;
        lastActionTime = now;
    }, true);

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.checkValidity && !form.checkValidity()) return;

        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }
        form.dataset.submitting = 'true';
        
        const btns = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type="button"]):not([type="reset"])');
        btns.forEach(btn => lockElement(btn));
        
        np.start();

        setTimeout(() => {
            if (form.dataset.submitting === 'true') {
                form.dataset.submitting = 'false';
                btns.forEach(btn => unlockElement(btn));
                np.done();
            }
        }, 12000);
    });

    const originalFetch = window.fetch;
    window.fetch = async function (...args) {
        const triggeringEl = lastActionEl || document.activeElement;
        const shouldLock = triggeringEl && (triggeringEl.tagName === 'BUTTON' || triggeringEl.tagName === 'A' || triggeringEl.getAttribute('role') === 'button');
        
        if (shouldLock) {
            if (triggeringEl.dataset.locked === 'true') {
                return new Promise(() => {});
            }
            lockElement(triggeringEl);
        }
        
        np.start();
        try {
            return await originalFetch.apply(this, args);
        } finally {
            if (shouldLock) {
                setTimeout(() => unlockElement(triggeringEl), 300);
            }
            np.done();
        }
    };

    if (typeof window.axios !== 'undefined') {
        window.axios.interceptors.request.use((config) => {
            const triggeringEl = lastActionEl || document.activeElement;
            if (triggeringEl && (triggeringEl.tagName === 'BUTTON' || triggeringEl.tagName === 'A')) {
                lockElement(triggeringEl);
                config.__lockedEl = triggeringEl;
            }
            np.start();
            return config;
        }, (error) => {
            np.done();
            return Promise.reject(error);
        });

        window.axios.interceptors.response.use((response) => {
            if (response.config && response.config.__lockedEl) {
                setTimeout(() => unlockElement(response.config.__lockedEl), 300);
            }
            np.done();
            return response;
        }, (error) => {
            if (error.config && error.config.__lockedEl) {
                setTimeout(() => unlockElement(error.config.__lockedEl), 300);
            }
            np.done();
            return Promise.reject(error);
        });
    }

    window.addEventListener('load', () => unlockAll());
    window.addEventListener('pageshow', (e) => unlockAll());

    function orderSearch() {
        return {
            query: '',
            results: [],
            total: 0,
            hasMore: false,
            open: false,
            loading: false,
            loadingMore: false,
            page: 1,
            async fetchResults() {
                const q = this.query.trim();
                if (q.length < 1) {
                    this.results = []; this.total = 0; this.hasMore = false; this.open = false; return;
                }
                this.loading = true; this.page = 1;
                try {
                    const res = await fetch(`/orders/search?q=${encodeURIComponent(q)}&limit=5&page=1`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    this.results = Array.isArray(data) ? data : (data.items || []);
                    this.total = data.total || this.results.length;
                    this.hasMore = data.has_more || false;
                    this.open = this.results.length > 0;
                } catch (e) {
                    this.results = []; this.hasMore = false;
                } finally {
                    this.loading = false;
                }
            },
            async loadMore() {
                if (this.loadingMore || !this.hasMore) return;
                this.loadingMore = true;
                try {
                    const nextPage = this.page + 1;
                    const res = await fetch(`/orders/search?q=${encodeURIComponent(this.query.trim())}&limit=5&page=${nextPage}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    const nextItems = Array.isArray(data) ? data : (data.items || []);
                    if (nextItems.length > 0) {
                        this.results = [...this.results, ...nextItems];
                        this.page = nextPage;
                        this.hasMore = data.has_more || false;
                    } else {
                        this.hasMore = false;
                    }
                } catch (e) {
                } finally {
                    this.loadingMore = false;
                }
            },
            close() {
                this.open = false; this.query = ''; this.results = []; this.total = 0; this.hasMore = false;
            }
        };
    }
</script>

