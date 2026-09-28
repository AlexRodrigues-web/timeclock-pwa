document.addEventListener('DOMContentLoaded', function () {
    const currentTime = document.getElementById('current-time');
    const entryClock = document.getElementById('entry_clock');
    const editTimeBtn = document.getElementById('edit-time-btn');

    const quickStoreOpen = document.getElementById('quick-store-open');
    const quickStoreModal = document.getElementById('quick-store-modal');
    const quickStoreCancel = document.getElementById('quick-store-cancel');
    const floatingFinish = document.getElementById('floating-finish-message');

    const sidebar = document.getElementById('sidebar');
    const sidebarOpen = document.getElementById('sidebar-open');
    const mobileBackdrop = document.getElementById('mobile-backdrop');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');

    const installAppBtn = document.getElementById('install-app-btn');
    let deferredInstallPrompt = null;

    const mainSubmit = document.getElementById('main-submit');
    const stage2Choices = document.querySelectorAll('input[name="stage2_choice"]');

    function nowParts() {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        return { hh, mm };
    }

    function isStandaloneMode() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }

    function hideInstallButton() {
        if (!installAppBtn) return;
        installAppBtn.hidden = true;
        installAppBtn.classList.remove('show');
    }

    function showInstallButton() {
        if (!installAppBtn || isStandaloneMode()) return;
        installAppBtn.hidden = false;
        requestAnimationFrame(() => {
            installAppBtn.classList.add('show');
        });
    }

    if (isStandaloneMode()) {
        hideInstallButton();
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredInstallPrompt = event;
        showInstallButton();
    });

    installAppBtn?.addEventListener('click', async function () {
        if (!deferredInstallPrompt) return;

        deferredInstallPrompt.prompt();
        const choiceResult = await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;

        if (choiceResult?.outcome === 'accepted') {
            hideInstallButton();
        }
    });

    window.addEventListener('appinstalled', function () {
        deferredInstallPrompt = null;
        hideInstallButton();
    });

    const syncCurrentClockOnLoad = () => {
        const { hh, mm } = nowParts();
        if (currentTime) {
            currentTime.textContent = `${hh}:${mm}`;
        }
        if (entryClock && !entryClock.disabled) {
            entryClock.value = `${hh}:${mm}`;
        }
    };

    syncCurrentClockOnLoad();

    if (currentTime) {
        setInterval(() => {
            const { hh, mm } = nowParts();
            currentTime.textContent = `${hh}:${mm}`;
        }, 1000);
    }

    if (editTimeBtn && entryClock && !editTimeBtn.disabled) {
        entryClock.readOnly = true;
        editTimeBtn.addEventListener('click', function () {
            entryClock.readOnly = false;
            entryClock.focus();
            if (typeof entryClock.showPicker === 'function') {
                entryClock.showPicker();
            }
        });
    }

    function refreshStage2Button() {
        if (!mainSubmit || stage2Choices.length === 0 || mainSubmit.disabled) return;
        const checked = document.querySelector('input[name="stage2_choice"]:checked');
        mainSubmit.textContent = checked && checked.value === 'saida' ? 'Finalizar dia' : 'Confirmar';
    }

    stage2Choices.forEach((item) => {
        item.addEventListener('change', refreshStage2Button);
    });
    refreshStage2Button();

    quickStoreOpen?.addEventListener('click', function () {
        quickStoreModal?.classList.add('show');
    });

    quickStoreCancel?.addEventListener('click', function () {
        quickStoreModal?.classList.remove('show');
    });

    quickStoreModal?.addEventListener('click', function (event) {
        if (event.target === quickStoreModal) {
            quickStoreModal.classList.remove('show');
        }
    });

    if (floatingFinish) {
        setTimeout(() => {
            floatingFinish.remove();
        }, 3200);
    }

    function openSidebar() {
        sidebar?.classList.add('open');
        mobileBackdrop?.classList.add('show');
        document.body.classList.add('sidebar-opened');
        sidebarOpen?.classList.add('is-active');
        sidebarOpen?.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        sidebar?.classList.remove('open');
        mobileBackdrop?.classList.remove('show');
        document.body.classList.remove('sidebar-opened');
        sidebarOpen?.classList.remove('is-active');
        sidebarOpen?.setAttribute('aria-expanded', 'false');
    }

    sidebarOpen?.addEventListener('click', function () {
        if (sidebar?.classList.contains('open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    mobileBackdrop?.addEventListener('click', closeSidebar);

    sidebarLinks.forEach((link) => {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 960) {
                closeSidebar();
            }
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 960) {
            closeSidebar();
        }
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/auditor-app/public/service-worker.js');
        });
    }
});
