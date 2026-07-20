<div id="pwa-install-banner" style="display: none; position: fixed; bottom: -100px; right: 20px; background-color: var(--rd-surface, #13161e); border: 1px solid var(--rd-border, #252a38); border-radius: 12px; padding: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5); z-index: 10000; width: 320px; transition: bottom 0.5s ease-out;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <div style="display: flex; align-items: center;">
            <img src="{{ asset('images/icons/icon-192.png') }}" alt="RDWIS icon" style="width: 45px; height: 45px; border-radius: 8px; margin-right: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
            <div>
                <strong style="color: var(--rd-text1, #e8ecf4); display: block; font-size: 16px;">RDWIS</strong>
                <span style="color: var(--rd-text2, #8b91a8); font-size: 12px;">Desktop App</span>
            </div>
        </div>
        <button id="pwa-dismiss-btn" style="background: none; border: none; color: var(--rd-text3, #555c74); cursor: pointer; font-size: 22px; padding: 0; line-height: 1;">&times;</button>
    </div>
    <button id="pwa-install-btn" style="width: 100%; background-color: var(--rd-accent, #4f8cff); color: #fff; border: none; border-radius: 7px; padding: 10px 0; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.2s;">Install App</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let deferredPrompt;
        const installBanner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');
        
        // Ensure dismiss action overrides
        const lastDismissed = localStorage.getItem('pwa-prompt-dismissed');
        if (lastDismissed) {
            const dismissedDate = new Date(parseInt(lastDismissed, 10));
            const now = new Date();
            const daysSinceDismissed = (now - dismissedDate) / (1000 * 60 * 60 * 24);
            
            if (daysSinceDismissed < 7) {
                return;
            }
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            setTimeout(() => {
                installBanner.style.display = 'block';
                void installBanner.offsetWidth; // triggle reflow
                installBanner.style.bottom = '20px';
            }, 30000);
        });

        installBtn.addEventListener('click', (e) => {
            installBanner.style.bottom = '-100px';
            setTimeout(() => { installBanner.style.display = 'none'; }, 500);
            
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    deferredPrompt = null;
                });
            }
        });

        dismissBtn.addEventListener('click', (e) => {
            installBanner.style.bottom = '-100px';
            setTimeout(() => { installBanner.style.display = 'none'; }, 500);
            localStorage.setItem('pwa-prompt-dismissed', Date.now().toString());
        });
        
        window.addEventListener('appinstalled', (evt) => {
            installBanner.style.bottom = '-100px';
            setTimeout(() => { installBanner.style.display = 'none'; }, 500);
        });
    });
</script>
