document.addEventListener('DOMContentLoaded', function () {
    if (!document.body.classList.contains('has-startup-splash')) return;

    const splashAlreadyShown = sessionStorage.getItem('timeclock_startup_splash_shown') === '1';

    if (splashAlreadyShown) {
        document.body.classList.remove('has-startup-splash');
        document.body.classList.remove('startup-splash-hide');
        return;
    }

    window.addEventListener('load', function () {
        setTimeout(function () {
            document.body.classList.add('startup-splash-hide');

            setTimeout(function () {
                document.body.classList.remove('has-startup-splash', 'startup-splash-hide');
                sessionStorage.setItem('timeclock_startup_splash_shown', '1');
            }, 450);
        }, 550);
    });
});
