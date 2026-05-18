@php
    $sedToasts = $sedToasts ?? \App\Support\ToastMessages::collect();
@endphp
@if (! empty($sedToasts))
<div class="sed-toast-stack" id="sed-toast-stack" aria-live="polite" aria-atomic="true">
    @foreach ($sedToasts as $toast)
        <div class="sed-toast sed-toast--{{ $toast['type'] }}" role="status" data-sed-toast>
            <span class="sed-toast__message">{{ $toast['message'] }}</span>
            <button type="button" class="sed-toast__close" aria-label="Закрыть">&times;</button>
        </div>
    @endforeach
</div>
<script>
(function () {
    var stack = document.getElementById('sed-toast-stack');
    if (!stack) return;

    stack.querySelectorAll('[data-sed-toast]').forEach(function (toast, index) {
        toast.style.animationDelay = (index * 0.06) + 's';

        var close = toast.querySelector('.sed-toast__close');
        function dismiss() {
            toast.classList.add('sed-toast--hide');
            toast.addEventListener('animationend', function () {
                toast.remove();
                if (!stack.querySelector('[data-sed-toast]')) {
                    stack.remove();
                }
            }, { once: true });
        }

        if (close) {
            close.addEventListener('click', dismiss);
        }

        window.setTimeout(dismiss, 6000 + index * 400);
    });
})();
</script>
@endif
