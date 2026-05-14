@extends('layouts.app')

@section('title', $document->name.' — СЭД СТАВ')

@section('content')
@php
    $latestVer = (int) ($document->versions->max('version_number') ?? 0);
    $docEditBaseUrl = \Illuminate\Support\Str::before(request()->fullUrl(), '#');
    $deptIdsOld = old('departments', $document->departments->pluck('id')->all());
    $catEditOld = old('category_id', $document->category_id);
@endphp
<div class="sed-page-header">
    <p class="sed-muted" style="margin:0 0 0.35rem;">
        @if($document->category)
            <a href="{{ route('categories.show', $document->category) }}">← {{ $document->category->name }}</a>
            <span> · </span>
        @endif
        <a href="{{ route('documents.index') }}">Все документы</a>
    </p>
    <h1>{{ $document->name }}</h1>
    <p>Статус: <strong>{{ $document->statusLabel() }}</strong></p>
</div>

@if (session('status'))
    <p class="sed-muted" style="margin:0 0 0.75rem;">{{ session('status') }}</p>
@endif

@if ($errors->any())
    <div class="sed-card" style="margin-bottom:1rem;border-color:#e0b4b4;">
        <ul class="sed-muted" style="margin:0;padding-left:1.25rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="sed-doc-layout">
    <div class="sed-card" style="margin-bottom:0;">
        <div class="sed-doc-meta">
            <span><strong>Текущая версия:</strong> v{{ $latestVer }}</span>
            <span><strong>Автор:</strong> {{ $document->author?->full_name ?? $document->author?->name ?? '—' }}</span>
            <span><strong>Последний изменивший:</strong> {{ $document->lastEditor?->displayName() ?? '—' }}</span>
            <span><strong>Отделы:</strong>
                @if($document->departments->isEmpty())
                    —
                @else
                    {{ $document->departments->pluck('name')->join(', ') }}
                @endif
            </span>
        </div>
        <div class="sed-doc-preview-wrap sed-doc-preview-wrap--resizable" style="margin-top:1rem;" data-doc-preview data-storage-key="sedDocPreviewH-{{ $document->id }}">
            <div class="sed-doc-preview-viewport" data-doc-preview-viewport>
                @if($previewKind === 'pdf')
                    @include('partials.pdf-preview', [
                        'previewUrl' => route('documents.preview', $document),
                        'viewerId' => 'sed-pdf-viewer-'.$document->id,
                    ])
                @else
                    <div class="sed-doc-preview-viewport__fill">
                        @if($previewKind === 'image')
                            <img class="sed-doc-preview__img" src="{{ route('documents.preview', $document) }}" alt="Предпросмотр изображения">
                        @elseif($previewKind === 'text')
                            <iframe class="sed-doc-preview__frame sed-doc-preview__frame--text" src="{{ route('documents.preview', $document) }}" title="Предпросмотр текста"></iframe>
                        @else
                            <div class="sed-doc-preview sed-doc-preview--empty">
                                Предпросмотр недоступен. Скачайте файл.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <button type="button" class="sed-doc-preview-handle" data-doc-preview-handle title="Потянуть вверх или вниз, чтобы изменить высоту" aria-label="Изменить высоту области предпросмотра, потянув вверх или вниз">
                <span class="sed-doc-preview-handle__grip" aria-hidden="true"></span>
            </button>
        </div>
        <div style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:0.5rem;">
            <a class="sed-btn sed-btn--primary sed-btn--sm" href="{{ route('documents.download', $document) }}">Скачать</a>
            @if($canStartApproval)
                <a class="sed-btn sed-btn--primary sed-btn--sm" href="#sed-approval-send">Отправить на согласование</a>
            @endif
            <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ $docEditBaseUrl }}#sed-edit-document">Изменить</a>
            <form action="{{ route('documents.destroy', $document) }}" method="post" style="display:inline;" data-sed-confirm="Удалить документ?">
                @csrf
                @method('DELETE')
                <button type="submit" class="sed-btn sed-btn--danger sed-btn--sm">Удалить</button>
            </form>
        </div>
    </div>

    <div class="sed-split">
        <div class="sed-card sed-comments">
            <h2>Комментарии</h2>
            @if (session('comment_status'))
                <p class="sed-muted" style="margin:0 0 0.75rem;">{{ session('comment_status') }}</p>
            @endif
            <form method="post" action="{{ route('documents.comments.store', $document) }}" style="margin-bottom:1rem;">
                @csrf
                <div class="sed-field" style="margin-bottom:0.5rem;">
                    <label for="sed-doc-comment-body">Новый комментарий</label>
                    <textarea class="sed-textarea" id="sed-doc-comment-body" name="body" rows="3" required maxlength="10000" placeholder="Текст комментария">{{ old('body') }}</textarea>
                </div>
                @error('body')
                    <p class="sed-muted" style="margin:0 0 0.5rem;color:#a33;">{{ $message }}</p>
                @enderror
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Отправить</button>
            </form>
            <ul class="sed-comment-thread" style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:0.75rem;">
                @forelse($document->comments as $comment)
                    <li style="padding:0.65rem 0;@if(!$loop->last) border-bottom:1px solid var(--border-color);@endif">
                        <div style="display:flex;justify-content:space-between;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.35rem;font-size:0.85rem;">
                            <strong>{{ $comment->user?->displayName() ?? '—' }}</strong>
                            <span class="sed-muted">{{ $comment->created_at?->format('d.m.Y H:i') }}</span>
                        </div>
                        <div style="white-space:pre-wrap;word-break:break-word;font-size:0.92rem;line-height:1.45;">{{ $comment->body }}</div>
                    </li>
                @empty
                    <li class="sed-muted" style="padding:0.25rem 0;border:0;">Комментариев пока нет.</li>
                @endforelse
            </ul>
        </div>
        <div class="sed-card sed-versions">
            <h2>Версии</h2>
            <ul class="sed-version-list">
                @forelse($document->versions as $v)
                    <li>
                        <div style="flex:1;min-width:0;">
                            <div>
                                <strong>v{{ $v->version_number }}</strong> — {{ $v->created_at?->format('d.m.Y, H:i') ?? '—' }}
                                @if($v->file_name)
                                    <span> · {{ $v->file_name }}</span>
                                @endif
                            </div>
                            @if($v->change_comment)
                                <div style="margin-top:0.25rem;font-size:0.85rem;line-height:1.35;">{{ \Illuminate\Support\Str::limit($v->change_comment, 200) }}</div>
                            @endif
                        </div>
                        @if($v->file_url)
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.35rem;flex-wrap:wrap;">
                                @if($v->version_number === $latestVer && $latestVer > 0)
                                    <span class="sed-badge">текущая</span>
                                @endif
                                <a class="sed-btn sed-btn--ghost sed-btn--sm" href="{{ route('documents.versions.download', [$document, $v]) }}">Скачать</a>
                            </div>
                        @else
                            <span class="sed-muted">—</span>
                        @endif
                    </li>
                @empty
                    <li><span>Версий пока нет</span><span></span></li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="sed-doc-approvals-below">
@include('documents.partials.approval-panel', [
    'docBaseUrl' => $docEditBaseUrl,
    'canStartApproval' => $canStartApproval,
    'approvalActionStep' => $approvalActionStep ?? null,
    'pastApprovalProcesses' => $pastApprovalProcesses ?? collect(),
])
</div>

<div id="sed-edit-document" class="sed-target-modal" role="dialog" aria-modal="true" aria-labelledby="sed-edit-document-title">
    <a href="{{ $docEditBaseUrl }}" class="sed-target-modal__scrim" aria-label="Закрыть"></a>
    <div class="sed-modal-panel sed-modal-panel--center sed-target-modal__inner">
        <div class="sed-modal-panel__head">
            <h2 id="sed-edit-document-title" class="sed-modal-panel__title">Изменить документ</h2>
            <a href="{{ $docEditBaseUrl }}" class="sed-modal-close" title="Закрыть" aria-label="Закрыть">&times;</a>
        </div>
        <form class="sed-modal-panel__body" method="post" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="sed-field">
                <label for="sed-doc-edit-name">Название <span class="sed-req" aria-hidden="true">*</span></label>
                <input class="sed-input" type="text" id="sed-doc-edit-name" name="name" required maxlength="255" value="{{ old('name', $document->name) }}">
            </div>
            <div class="sed-field">
                <label for="sed-doc-edit-category">Папка</label>
                <select class="sed-input" id="sed-doc-edit-category" name="category_id">
                    <option value="" @selected($catEditOld === null || $catEditOld === '')>Без папки (корень)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) $catEditOld === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <fieldset class="sed-fieldset">
                <legend class="sed-fieldset__legend">Виден отделам <span class="sed-req" aria-hidden="true">*</span></legend>
                <div class="sed-dept-checkboxes">
                    @foreach($departments as $d)
                        <label class="sed-check-row">
                            <input type="checkbox" name="departments[]" value="{{ $d->id }}" @checked(in_array((int) $d->id, array_map('intval', (array) $deptIdsOld), true))>
                            <span>{{ $d->name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="sed-field">
                <label for="sed-doc-edit-file">Новый файл (необязательно)</label>
                <input class="sed-input sed-input--file" type="file" id="sed-doc-edit-file" name="file"
                    accept=".pdf,.doc,.docx,.txt,.xlsx,.xls,.ppt,.pptx,.odt,.ods,.rtf,.csv,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
            </div>
            <div class="sed-field" style="margin-bottom:0;">
                <label for="sed-doc-edit-comment">Комментарий к новой версии</label>
                <textarea class="sed-textarea" id="sed-doc-edit-comment" name="change_comment" rows="2" placeholder="При новой версии файла (необязательно)">{{ old('change_comment') }}</textarea>
            </div>
            <div class="sed-modal-panel__foot sed-modal-panel__foot--form">
                <a href="{{ $docEditBaseUrl }}" class="sed-btn sed-btn--ghost sed-btn--sm">Отмена</a>
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Сохранить</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var root = document.querySelector('[data-doc-preview]');
    var viewport = root && root.querySelector('[data-doc-preview-viewport]');
    var handle = root && root.querySelector('[data-doc-preview-handle]');
    if (!root || !viewport || !handle) return;

    var storageKey = root.getAttribute('data-storage-key') || 'sedDocPreviewH';
    var minH = 180;
    function maxH() {
        return Math.max(minH, window.innerHeight - 96);
    }
    function defaultH() {
        return Math.min(560, Math.round(window.innerHeight * 0.62));
    }
    function clamp(n) {
        return Math.min(Math.max(Math.round(n), minH), maxH());
    }
    function apply(px) {
        var v = clamp(px);
        root.style.setProperty('--sed-doc-preview-height', v + 'px');
        try {
            localStorage.setItem(storageKey, String(v));
        } catch (e) {}
        window.dispatchEvent(new Event('resize'));
    }
    function readSaved() {
        try {
            var s = localStorage.getItem(storageKey);
            if (s == null || s === '') return null;
            var n = parseInt(s, 10);
            return isNaN(n) ? null : n;
        } catch (e) {
            return null;
        }
    }
    var saved = readSaved();
    apply(saved != null ? saved : defaultH());

    window.addEventListener('resize', function (e) {
        if (e.isTrusted === false) {
            return;
        }
        var cur = viewport.getBoundingClientRect().height;
        apply(cur);
    });

    handle.addEventListener('pointerdown', function (e) {
        if (e.button !== 0) return;
        e.preventDefault();
        var startY = e.clientY;
        var startH = viewport.getBoundingClientRect().height;
        var pid = e.pointerId;
        try {
            handle.setPointerCapture(pid);
        } catch (err) {}

        function onMove(ev) {
            apply(startH + (ev.clientY - startY));
        }
        function onUp(ev) {
            try {
                handle.releasePointerCapture(pid);
            } catch (err2) {}
            handle.removeEventListener('pointermove', onMove);
            handle.removeEventListener('pointerup', onUp);
            handle.removeEventListener('pointercancel', onUp);
            window.dispatchEvent(new Event('resize'));
        }
        handle.addEventListener('pointermove', onMove);
        handle.addEventListener('pointerup', onUp);
        handle.addEventListener('pointercancel', onUp);
    });

    handle.addEventListener('keydown', function (e) {
        if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') return;
        e.preventDefault();
        var cur = viewport.getBoundingClientRect().height;
        apply(cur + (e.key === 'ArrowDown' ? 24 : -24));
    });
})();
</script>
@endsection
