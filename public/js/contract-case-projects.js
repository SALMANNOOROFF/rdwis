(() => {
    const start = () => {
        const panel = document.getElementById('contractProjectPanel');
        if (!panel) return;
        const buttons = Array.from(document.querySelectorAll('[data-project-panel]'));
        let request;
        let version = 0;

        async function load(button) {
            request?.abort();
            request = new AbortController();
            const currentVersion = ++version;
            const head = button.dataset.headId;
            const section = button.dataset.projectPanel;
            document.querySelectorAll('[data-allocation-row]').forEach(row => {
                row.classList.toggle('is-selected', row.dataset.allocationRow === head);
            });
            buttons.forEach(item => {
                const selected = item.dataset.headId === head && item.dataset.projectPanel === section;
                item.setAttribute('aria-pressed', String(selected));
                item.classList.toggle('active', selected);
            });
            panel.setAttribute('aria-busy', 'true');
            panel.innerHTML = '<div class="py-4 text-muted" role="status"><i class="fas fa-spinner fa-spin mr-2"></i>Loading selected project…</div>';
            try {
                const url = new URL(button.dataset.panelUrl, window.location.href);
                url.searchParams.set('section', section);
                const response = await fetch(url, {
                    signal: request.signal,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                });
                if (!response.ok || response.redirected) throw new Error('Project details could not be loaded. Please retry or refresh your session.');
                const html = await response.text();
                if (currentVersion !== version) return;
                panel.innerHTML = html;
            } catch (error) {
                if (error.name === 'AbortError' || currentVersion !== version) return;
                panel.replaceChildren();
                const message = document.createElement('p');
                message.className = 'text-danger';
                message.textContent = 'Project details could not be loaded. Please retry or refresh your session.';
                const retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'btn btn-sm btn-outline-primary';
                retry.textContent = 'Retry';
                retry.addEventListener('click', () => load(button));
                panel.append(message, retry);
            } finally {
                if (currentVersion === version) panel.setAttribute('aria-busy', 'false');
            }
        }

        buttons.forEach(button => button.addEventListener('click', () => load(button)));
        panel.addEventListener('click', event => {
            const link = event.target.closest('[data-project-document]');
            if (link && typeof window.openLiveDocument === 'function') {
                event.preventDefault();
                window.openLiveDocument(link.href, link.dataset.documentTitle);
            }
        });
        if (buttons.length) load(buttons[0]);
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
