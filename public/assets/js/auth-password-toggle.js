document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.password-toggle');

    toggles.forEach(function (button) {
        button.addEventListener('click', function () {
            const field = button.closest('.password-field');
            if (!field) return;

            const input = field.querySelector('input');
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            button.setAttribute('aria-label', isHidden ? 'Ocultar password' : 'Mostrar password');

            button.classList.toggle('is-visible', isHidden);
        });
    });
});
