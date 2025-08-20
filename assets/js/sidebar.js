window.addEventListener('load', () => {
    document.documentElement.classList.remove('preload');
  });
  
document.addEventListener('DOMContentLoaded', () => {

    // --- Centralized DOM Element Declarations ---
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburger');
    const barsIcon = document.getElementById('barsIcon');
    const xmarkIcon = document.getElementById('xmarkIcon');
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    const mainContentWrapper = document.getElementById('mainContentWrapper');

    // --- Lightweight PJAX to keep sidebar persistent ---
    if (!window.__loadedScriptSrcs) {
        window.__loadedScriptSrcs = new Set();
    }

    function isSameOriginAbsoluteUrl(url) {
        try { const u = new URL(url, window.location.href); return u.origin === window.location.origin; } catch (_) { return false; }
    }

    function updateActiveSidebarLink(url) {
        try {
            const u = new URL(url, window.location.href);
            const currentFileName = u.pathname.substring(u.pathname.lastIndexOf('/') + 1);
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#') return;
                const linkFileName = new URL(link.href).pathname.split('/').pop();
                if (linkFileName === currentFileName) link.classList.add('active');
            });
            // Keep only the dropdown that contains the active link open
            const activeLink = Array.from(document.querySelectorAll('.sidebar a')).find(a => a.classList.contains('active'));
            const dropdownContents = document.querySelectorAll('.sidebar-dropdown-content');
            if (activeLink) {
                let keepId = null;
                dropdownContents.forEach(content => {
                    if (content.contains(activeLink)) {
                        keepId = content.id;
                    }
                });
                dropdownContents.forEach(content => {
                    if (content.id === keepId) {
                        content.classList.add('show');
                        const t = document.querySelector(`.sidebar-dropdown-toggle[data-target="${content.id}"]`);
                        if (t) t.classList.add('active');
                    } else {
                        content.classList.remove('show');
                        const t = document.querySelector(`.sidebar-dropdown-toggle[data-target="${content.id}"]`);
                        if (t) t.classList.remove('active');
                    }
                });
            } else {
                // No active link in dropdowns: close all
                dropdownContents.forEach(content => {
                    content.classList.remove('show');
                    const t = document.querySelector(`.sidebar-dropdown-toggle[data-target="${content.id}"]`);
                    if (t) t.classList.remove('active');
                });
            }
        } catch (_) { /* no-op */ }
    }

    async function executeScriptsFrom(containerNode, baseUrl) {
        const scriptNodes = Array.from(containerNode.querySelectorAll('script'));
        // Remove original script nodes to avoid duplicate DOM
        scriptNodes.forEach(s => s.parentNode && s.parentNode.removeChild(s));
        // Sequentially load scripts to preserve order
        for (const scriptNode of scriptNodes) {
            const newScript = document.createElement('script');
            // Copy attributes
            for (const attr of scriptNode.attributes) {
                newScript.setAttribute(attr.name, attr.value);
            }
            if (scriptNode.src) {
                const absSrc = new URL(scriptNode.getAttribute('src'), baseUrl).href;
                if (window.__loadedScriptSrcs.has(absSrc)) {
                    continue; // skip already loaded external scripts
                }
                newScript.src = absSrc;
                await new Promise((resolve, reject) => {
                    newScript.onload = () => { window.__loadedScriptSrcs.add(absSrc); resolve(); };
                    newScript.onerror = () => resolve(); // fail silently; page should still render
                    document.body.appendChild(newScript);
                });
            } else {
                newScript.text = scriptNode.textContent || '';
                document.body.appendChild(newScript);
            }
        }
    }

    async function pjaxNavigate(url, addToHistory = true) {
        if (!isSameOriginAbsoluteUrl(url)) { window.location.href = url; return; }
        document.body.classList.add('pjax-loading');
        try {
            const response = await fetch(url, { credentials: 'same-origin' });
            const htmlText = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            const newWrapper = doc.querySelector('#mainContentWrapper');
            const currentWrapper = document.getElementById('mainContentWrapper');
            if (newWrapper && currentWrapper) {
                // Apply persisted sidebar state (mirrors inline snippets)
                try {
                    const savedCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    sidebar.classList.toggle('collapsed', savedCollapsed);
                    currentWrapper.classList.toggle('expanded', savedCollapsed);
                    document.body.classList.toggle('sidebar-active', !savedCollapsed);
                } catch (_) {}

                // Clone to avoid moving nodes across documents
                const imported = document.importNode(newWrapper, true);
                currentWrapper.replaceWith(imported);

                // Replace page-specific modals outside the wrapper
                try {
                    // Remove existing page modals (keep global alert)
                    document.querySelectorAll('body > .modal').forEach(m => {
                        if (m.id !== 'customAlert') m.remove();
                    });
                    // Remove any transient shared UI overlays to prevent cross-page handler conflicts
                    const staleDatepicker = document.getElementById('shared-datepicker');
                    if (staleDatepicker) staleDatepicker.remove();
                    const staleSelect = document.getElementById('shared-select-options');
                    if (staleSelect) staleSelect.remove();
                    // Append new modals from fetched doc that are not inside wrapper
                    doc.querySelectorAll('body > .modal').forEach(m => {
                        if (m.id !== 'customAlert') {
                            document.body.appendChild(document.importNode(m, true));
                        }
                    });
                } catch (_) {}

                // Execute all scripts discovered in fetched document (external loaded once)
                await executeScriptsFrom(doc, url);

                // Re-init global UI and mark active link
                if (typeof window.initGlobalUI === 'function') {
                    window.initGlobalUI();
                }
                // Call page-specific initializer based on target URL (run defensively twice for timing)
                try {
                    const page = new URL(url, window.location.href).pathname.split('/').pop();
                    const pageInitMap = {
                        // References to deleted modules removed
                    };
                    const initName = pageInitMap[page];
                    if (initName && typeof window[initName] === 'function') {
                        window[initName]();
                        requestAnimationFrame(() => { try { window[initName](); } catch (_) {} });
                        setTimeout(() => { try { window[initName](); } catch (_) {} }, 0);
                    }
                } catch (_) {}
                updateActiveSidebarLink(url);

                // Update title
                if (doc.title) { document.title = doc.title; }

                if (addToHistory) {
                    history.pushState({ url }, '', url);
                }

                window.scrollTo({ top: 0 });
            } else {
                // Fallback to full navigation
                window.location.href = url;
            }
        } catch (e) {
            window.location.href = url;
        } finally {
            document.body.classList.remove('pjax-loading');
        }
    }

    // Apply persisted sidebar state
    try {
        const savedCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebar && mainContentWrapper) {
            sidebar.classList.toggle('collapsed', savedCollapsed);
            mainContentWrapper.classList.toggle('expanded', savedCollapsed);
            document.body.classList.toggle('sidebar-active', !savedCollapsed);
            // Clean any initial classes possibly added pre-render
            sidebar.classList.remove('initial-collapsed');
            mainContentWrapper.classList.remove('initial-expanded');
        }
    } catch (_) { /* no-op */ }

    // Sidebar Toggle with delegated handler (so it works after PJAX)
    function syncSidebarStateClasses() {
        if (!(sidebar && mainContentWrapper)) return;
        if (sidebar.classList.contains('collapsed')) {
            document.body.classList.remove('sidebar-active');
            mainContentWrapper.classList.add('expanded');
        } else {
            document.body.classList.add('sidebar-active');
            mainContentWrapper.classList.remove('expanded');
        }
    }
    syncSidebarStateClasses();

    if (!window.__hamburgerDelegated) {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('#hamburger');
            if (!btn) return;
            const wrapper = document.getElementById('mainContentWrapper');
            if (!(sidebar && wrapper)) return;
            sidebar.classList.toggle('collapsed');
            wrapper.classList.toggle('expanded');
            document.body.classList.toggle('sidebar-active');
            try { localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); } catch(_) {}
        });
        window.__hamburgerDelegated = true;
    }

    // Active Sidebar Link Highlighting + PJAX intercept
    if (sidebarLinks.length > 0) {
        updateActiveSidebarLink(window.location.href);
        sidebarLinks.forEach(link => {
            if (link.getAttribute('href') === '#') return;
            link.addEventListener('click', (e) => {
                if (link.textContent && link.textContent.trim() === 'Logout') return;
                e.preventDefault();
                pjaxNavigate(link.href, true);
            });
        });
        window.addEventListener('popstate', (e) => {
            const targetUrl = (e.state && e.state.url) ? e.state.url : window.location.href;
            pjaxNavigate(targetUrl, false);
        });
    }
});
