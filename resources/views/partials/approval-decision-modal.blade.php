@php
    $mid = $modalId ?? 'sed-approval-decision';
@endphp
<div id="{{ $mid }}" class="sed-target-modal" role="dialog" aria-modal="true" aria-labelledby="{{ $mid }}-title">
    <a href="{{ $closeHref }}" class="sed-target-modal__scrim" aria-label="Закрыть"></a>
    <div class="sed-modal-panel sed-modal-panel--center sed-target-modal__inner" role="document">
        <div class="sed-modal-panel__head">
            <h2 id="{{ $mid }}-title" class="sed-modal-panel__title sed-approval-decision-modal__title">Решение</h2>
            <a href="{{ $closeHref }}" class="sed-modal-close" title="Закрыть" aria-label="Закрыть">&times;</a>
        </div>
        <form id="{{ $mid }}-form" method="post" class="sed-modal-panel__body sed-approval-decision-form" action="#" autocomplete="off">
            @csrf
            <input type="hidden" name="return_fragment" value="{{ $mid }}">
            @if(!empty($subtitle))
                <p style="margin:0 0 0.75rem;">{{ $subtitle }}</p>
            @endif
            <div class="sed-field">
                <label for="{{ $mid }}-comment">Комментарий</label>
                <textarea class="sed-textarea" id="{{ $mid }}-comment" name="comment" rows="4" maxlength="2000" placeholder="Необязательно">{{ old('comment') }}</textarea>
            </div>
            <div class="sed-field" style="margin-bottom:0;">
                <label for="{{ $mid }}-pin">Электронная подпись <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" id="{{ $mid }}-pin" name="signature_pin" type="password" inputmode="numeric" pattern="\d{4,6}" maxlength="6" autocomplete="one-time-code" required placeholder="4–6 цифр">
            </div>
            <div class="sed-modal-panel__foot sed-modal-panel__foot--form" style="margin-top:0.85rem;padding-top:0.85rem;border-top:1px solid var(--border-color,#ddd);">
                <a href="{{ $closeHref }}" class="sed-btn sed-btn--ghost sed-btn--sm">Отмена</a>
                <button type="submit" class="sed-btn sed-btn--sm sed-approval-decision-modal__submit sed-btn--primary">Подтвердить</button>
            </div>
        </form>
    </div>
</div>
