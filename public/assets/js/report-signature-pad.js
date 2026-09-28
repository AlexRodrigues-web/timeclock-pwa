document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.querySelector('[data-signature-builder]');
    if (!wrapper) return;

    const yesRadio = wrapper.querySelector('#use_drawn_signature_yes');
    const noRadio = wrapper.querySelector('#use_drawn_signature_no');
    const panel = wrapper.querySelector('.signature-builder-panel');
    const canvas = wrapper.querySelector('#signature-pad');
    const hiddenInput = wrapper.querySelector('#signature_data');
    const saveBtn = wrapper.querySelector('#signature-save-btn');
    const clearBtn = wrapper.querySelector('#signature-clear-btn');
    const status = wrapper.querySelector('#signature-status');
    const form = wrapper.closest('form');

    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let drawing = false;
    let hasStroke = false;

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();
        const savedImage = hasStroke ? canvas.toDataURL('image/png') : null;

        canvas.width = Math.floor(rect.width * ratio);
        canvas.height = Math.floor(rect.height * ratio);
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(ratio, ratio);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.lineWidth = 2.2;
        ctx.strokeStyle = '#111827';

        ctx.clearRect(0, 0, rect.width, rect.height);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, rect.width, rect.height);

        if (savedImage) {
            const img = new Image();
            img.onload = function () {
                ctx.drawImage(img, 0, 0, rect.width, rect.height);
            };
            img.src = savedImage;
        }
    }

    function showPanel(show) {
        panel.hidden = !show;
        if (show) {
            setTimeout(resizeCanvas, 10);
        } else {
            hiddenInput.value = '';
            status.textContent = 'A usar assinatura guardada nas configurações, se existir.';
        }
    }

    function getPoint(event) {
        const rect = canvas.getBoundingClientRect();
        if (event.touches && event.touches.length > 0) {
            return {
                x: event.touches[0].clientX - rect.left,
                y: event.touches[0].clientY - rect.top
            };
        }

        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top
        };
    }

    function beginDraw(event) {
        drawing = true;
        const point = getPoint(event);
        ctx.beginPath();
        ctx.moveTo(point.x, point.y);
        event.preventDefault();
    }

    function draw(event) {
        if (!drawing) return;
        const point = getPoint(event);
        ctx.lineTo(point.x, point.y);
        ctx.stroke();
        hasStroke = true;
        event.preventDefault();
    }

    function endDraw(event) {
        if (!drawing) return;
        drawing = false;
        ctx.closePath();
        event.preventDefault();
    }

    function clearSignature() {
        hasStroke = false;
        hiddenInput.value = '';
        resizeCanvas();
        status.textContent = 'Assinatura limpa.';
    }

    function saveSignature() {
        if (!hasStroke) {
            status.textContent = 'Assina primeiro antes de guardar.';
            return false;
        }

        hiddenInput.value = canvas.toDataURL('image/png');
        status.textContent = 'Assinatura guardada para este relatório.';
        return true;
    }

    yesRadio?.addEventListener('change', function () {
        if (yesRadio.checked) {
            showPanel(true);
            status.textContent = 'Assina no quadro e clica em "Guardar assinatura".';
        }
    });

    noRadio?.addEventListener('change', function () {
        if (noRadio.checked) {
            showPanel(false);
        }
    });

    saveBtn?.addEventListener('click', function () {
        saveSignature();
    });

    clearBtn?.addEventListener('click', function () {
        clearSignature();
    });

    canvas.addEventListener('mousedown', beginDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', endDraw);
    canvas.addEventListener('mouseleave', endDraw);

    canvas.addEventListener('touchstart', beginDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', endDraw, { passive: false });

    window.addEventListener('resize', function () {
        if (!panel.hidden) {
            resizeCanvas();
        }
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            if (yesRadio && yesRadio.checked) {
                if (!saveSignature()) {
                    event.preventDefault();
                }
            }
        });
    }

    showPanel(!!yesRadio?.checked);
    resizeCanvas();
});
