<div id="sed-confirm-modal" class="sed-target-modal" role="dialog" aria-modal="true" aria-labelledby="sed-confirm-modal-title" aria-hidden="true">
    <button type="button" class="sed-target-modal__scrim" data-sed-confirm-cancel aria-label="Закрыть"></button>
    <div class="sed-modal-panel sed-modal-panel--center sed-target-modal__inner">
        <div class="sed-modal-panel__head">
            <h2 id="sed-confirm-modal-title" class="sed-modal-panel__title">Подтверждение</h2>
            <button type="button" class="sed-modal-close" data-sed-confirm-cancel title="Закрыть" aria-label="Закрыть">&times;</button>
        </div>
        <div class="sed-modal-panel__body">
            <p id="sed-confirm-modal-text" class="sed-muted" style="margin:0;line-height:1.45;"></p>
        </div>
        <div class="sed-modal-panel__foot sed-modal-panel__foot--form">
            <button type="button" class="sed-btn sed-btn--ghost sed-btn--sm" data-sed-confirm-cancel>Отмена</button>
            <button type="button" class="sed-btn sed-btn--danger sed-btn--sm" data-sed-confirm-ok>Подтвердить</button>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('sed-confirm-modal');
    if (!modal) {
        return;
    }
    var textEl = document.getElementById('sed-confirm-modal-text');
    var okBtn = modal.querySelector('[data-sed-confirm-ok]');
    var pendingForm = null;
    var lastFocus = null;

    function isOpen() {
        return modal.classList.contains('sed-target-modal--js-open');
    }

    function open(msg, form) {
        lastFocus = document.activeElement;
        pendingForm = form;
        if (textEl) {
            textEl.textContent = msg;
        }
        modal.classList.add('sed-target-modal--js-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (okBtn && typeof okBtn.focus === 'function') {
            window.setTimeout(function () {
                okBtn.focus();
            }, 0);
        }
    }

    function close() {
        pendingForm = null;
        modal.classList.remove('sed-target-modal--js-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
        lastFocus = null;
    }

    function onOk() {
        if (!pendingForm) {
            close();
            return;
        }
        var f = pendingForm;
        pendingForm = null;
        close();
        f.setAttribute('data-sed-confirm-approved', '1');
        if (typeof f.requestSubmit === 'function') {
            f.requestSubmit();
        } else {
            f.submit();
        }
        f.removeAttribute('data-sed-confirm-approved');
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.nodeName !== 'FORM') {
            return;
        }
        if (form.getAttribute('data-sed-confirm-approved') === '1') {
            form.removeAttribute('data-sed-confirm-approved');
            return;
        }
        var msg = form.getAttribute('data-sed-confirm');
        if (!msg) {
            return;
        }
        e.preventDefault();
        open(msg, form);
    }, false);

    modal.addEventListener('click', function (e) {
        var t = e.target;
        if (!t || !t.closest) {
            return;
        }
        if (t.closest('[data-sed-confirm-cancel]')) {
            close();
            return;
        }
        if (t.closest('[data-sed-confirm-ok]')) {
            onOk();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !isOpen()) {
            return;
        }
        e.preventDefault();
        close();
    });
})();
</script>
