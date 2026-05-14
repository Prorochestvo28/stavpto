@php
    $mid = $modalId ?? 'sed-approval-decision';
    $useRow = $useRow ?? true;
    $am = $approvalModal ?? null;
@endphp
<script>
(function () {
    var mid = @json($mid);
    var useRow = @json($useRow);
    var form = document.getElementById(mid + '-form');
    var modal = document.getElementById(mid);
    if (!form || !modal) return;
    var titleEl = modal.querySelector('.sed-approval-decision-modal__title');
    var btn = modal.querySelector('.sed-approval-decision-modal__submit');

    function applyDecision(action, approveUrl, rejectUrl) {
        if (action === 'approve') {
            form.action = approveUrl;
            btn.textContent = 'Согласовать';
            btn.setAttribute('class', 'sed-btn sed-btn--sm sed-approval-decision-modal__submit sed-btn--primary');
            if (titleEl) titleEl.textContent = 'Согласование';
        } else {
            form.action = rejectUrl;
            btn.textContent = 'Отклонить';
            btn.setAttribute('class', 'sed-btn sed-btn--sm sed-approval-decision-modal__submit sed-btn--danger');
            if (titleEl) titleEl.textContent = 'Отклонение';
        }
    }

    document.querySelectorAll('a[href="#' + mid + '"].sed-approval-decision-trigger').forEach(function (a) {
        a.addEventListener('click', function () {
            var action = a.getAttribute('data-decision');
            var approveUrl;
            var rejectUrl;
            if (useRow) {
                var row = a.closest('tr[data-step-id]');
                approveUrl = row && row.getAttribute('data-approve-url');
                rejectUrl = row && row.getAttribute('data-reject-url');
            } else {
                approveUrl = a.getAttribute('data-approve-url');
                rejectUrl = a.getAttribute('data-reject-url');
            }
            if (!approveUrl || !rejectUrl) return;
            applyDecision(action, approveUrl, rejectUrl);
        });
    });

    @if(!empty($am) && !empty($am['step_id']) && !empty($am['action']))
    (function reopenFromSession() {
        var stepId = {{ (int) $am['step_id'] }};
        var action = @json($am['action']);
        var approveUrl;
        var rejectUrl;
        if (useRow) {
            var row = document.querySelector('tr[data-step-id="' + stepId + '"]');
            if (row) {
                approveUrl = row.getAttribute('data-approve-url');
                rejectUrl = row.getAttribute('data-reject-url');
            }
        } else {
            var trigger = document.querySelector(
                'a[href="#' + mid + '"].sed-approval-decision-trigger[data-decision="' + action + '"]'
            );
            if (trigger) {
                approveUrl = trigger.getAttribute('data-approve-url');
                rejectUrl = trigger.getAttribute('data-reject-url');
            }
        }
        if (!approveUrl || !rejectUrl) return;
        applyDecision(action, approveUrl, rejectUrl);
        if (window.location.hash.replace('#', '') !== mid) {
            window.location.hash = mid;
        }
    })();
    @endif
})();
</script>
