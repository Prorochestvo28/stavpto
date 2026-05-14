@php
    $proc = $document->activeApprovalProcess;
    $canStart = $canStartApproval ?? (
        $document->status === 'draft'
        && ! $proc
        && ((int) auth()->id() === (int) $document->author_id || auth()->user()->isAdmin())
    );
    $docBaseUrl = $docBaseUrl ?? \Illuminate\Support\Str::before(request()->fullUrl(), '#');
    $approvalActionStep = $approvalActionStep ?? null;
    $pastApprovalProcesses = isset($pastApprovalProcesses) ? $pastApprovalProcesses : collect();
@endphp

<div class="sed-approvals-doc-stack @if($pastApprovalProcesses->isNotEmpty()) sed-approvals-doc-stack--split @endif">
    <section class="sed-card sed-approval-doc" aria-labelledby="sed-approval-doc-heading">
        <h2 id="sed-approval-doc-heading" class="sed-approval-doc__title">Согласование</h2>
        <p class="sed-approval-doc__status">Статус документа: <strong>{{ $document->statusLabel() }}</strong></p>

        @if($document->status === 'approved')
            <p class="sed-approval-doc__text">Маршрут завершён, документ согласован.</p>
        @elseif($document->status === 'rejected')
            <p class="sed-approval-doc__text">Маршрут завершён с отклонением.</p>
            <form method="post" action="{{ route('documents.reopen-draft', $document) }}" class="sed-approval-doc__form">
                @csrf
                <button type="submit" class="sed-btn sed-btn--ghost sed-btn--sm">Вернуть в черновик</button>
            </form>
        @elseif($proc)
            <p class="sed-approval-doc__text">Текущий маршрут согласования.</p>
            <p class="sed-approval-doc__meta">
                Срок: <strong>@if($proc->deadline){{ $proc->deadline->timezone(config('app.timezone'))->format('d.m.Y') }}@else не задан@endif</strong>
                · Инициатор: <strong>{{ $proc->initiator?->displayName() ?? $proc->initiator?->email ?? '—' }}</strong>
            </p>
            <div class="sed-approval-doc__route">
                @include('documents.partials.approval-route-readonly', ['process' => $proc])
            </div>
        @elseif($canStart)
            <p class="sed-approval-doc__text">Черновик. Запуск — кнопка «Отправить на согласование» выше.</p>
        @else
            <p class="sed-approval-doc__text">Запуск согласования недоступен.</p>
        @endif

        @if($document->status === 'approved' && $pastApprovalProcesses->isNotEmpty())
            <p class="sed-approval-doc__hint">Прошлые маршруты — в колонке справа (на узком экране — ниже).</p>
        @endif

        @if($proc && $approvalActionStep && auth()->user()->hasSignaturePin())
            <div class="sed-approval-doc__actions">
                <a href="#sed-doc-approval-decision" class="sed-btn sed-btn--primary sed-btn--sm sed-approval-decision-trigger" data-decision="approve" data-approve-url="{{ route('approval-steps.approve', $approvalActionStep) }}" data-reject-url="{{ route('approval-steps.reject', $approvalActionStep) }}">Согласовать</a>
                <a href="#sed-doc-approval-decision" class="sed-btn sed-btn--danger sed-btn--sm sed-approval-decision-trigger" data-decision="reject" data-approve-url="{{ route('approval-steps.approve', $approvalActionStep) }}" data-reject-url="{{ route('approval-steps.reject', $approvalActionStep) }}">Отклонить</a>
            </div>
        @endif
    </section>

    @if($pastApprovalProcesses->isNotEmpty())
        @include('documents.partials.approval-processes-history', ['pastApprovalProcesses' => $pastApprovalProcesses])
    @endif
</div>

@if($approvalActionStep && auth()->user()->hasSignaturePin())
    @include('partials.approval-decision-modal', [
        'modalId' => 'sed-doc-approval-decision',
        'closeHref' => $docBaseUrl,
        'subtitle' => $document->name,
    ])
    @include('partials.approval-decision-modal-script', [
        'modalId' => 'sed-doc-approval-decision',
        'useRow' => false,
        'approvalModal' => (
            session('approval_modal')
            && $approvalActionStep
            && (int) (session('approval_modal')['step_id'] ?? 0) === (int) $approvalActionStep->id
        ) ? session('approval_modal') : null,
    ])
@endif

@if($canStart)
@php
    $tz = config('app.timezone');
    $approvalDeadlineDefault = now()->timezone($tz)->addWeek()->format('Y-m-d');
    $approvalDeadlineMin = now()->timezone($tz)->format('Y-m-d');
@endphp
<div id="sed-approval-send" class="sed-target-modal" role="dialog" aria-modal="true" aria-labelledby="sed-approval-send-title">
    <a href="{{ $docBaseUrl }}" class="sed-target-modal__scrim" aria-label="Закрыть"></a>
    <div class="sed-modal-panel sed-modal-panel--center sed-target-modal__inner" style="width:min(640px,calc(100vw - 2rem));max-height:min(92vh,760px);">
        <div class="sed-modal-panel__head">
            <h2 id="sed-approval-send-title" class="sed-modal-panel__title">Отправка на согласование</h2>
            <a href="{{ $docBaseUrl }}" class="sed-modal-close" title="Закрыть" aria-label="Закрыть">&times;</a>
        </div>
        <form method="post" action="{{ route('documents.approval-process.store', $document) }}" id="sed-approval-start-form" class="sed-modal-panel__body" style="display:flex;flex-direction:column;min-height:0;">
            @csrf
            <div class="sed-field" style="margin-bottom:0.65rem;">
                <label for="sed-approval-dept-filter">Отдел</label>
                <select class="sed-select" id="sed-approval-dept-filter">
                    <option value="">Все отделы</option>
                    <option value="__none__">Без отдела</option>
                    @isset($departments)
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="sed-field" style="margin-bottom:0.65rem;">
                <label for="sed-approval-user-search">Поиск</label>
                <input type="search" class="sed-input" id="sed-approval-user-search" autocomplete="off">
            </div>
            <div class="sed-field" style="margin-bottom:0.5rem;">
                <div style="margin-bottom:0.35rem;font-weight:600;">Порядок</div>
                <ul id="sed-approval-order-preview" style="margin:0;padding-left:0;list-style:none;font-size:0.9rem;min-height:1.25rem;"></ul>
            </div>
            <div id="sed-approval-assignee-hiddens" aria-hidden="true"></div>
            <div id="sed-approval-user-list" style="flex:1;min-height:0;overflow-y:auto;border:1px solid var(--border-color,#ddd);border-radius:6px;padding:0.5rem 0.65rem;margin-bottom:0.5rem;max-height:14rem;">
                @forelse($assignableUsers as $u)
                    @php
                        $deptName = $u->department?->name ?? '';
                        $searchHay = \Illuminate\Support\Str::lower(trim($u->displayName().' '.$u->email.' '.($u->name ?? '').' '.$deptName));
                    @endphp
                    <label class="sed-check-row sed-approval-user-row" data-user-id="{{ $u->id }}" data-search="{{ $searchHay }}" data-department-id="{{ $u->department_id ?? '' }}" style="margin:0.15rem 0;">
                        <input type="checkbox" class="sed-approval-user-cb" value="{{ $u->id }}" data-order-line="{{ e($u->displayName().' · '.$u->email.' — '.($deptName !== '' ? $deptName : 'без отдела')) }}">
                        <span>{{ $u->displayName() }} · {{ $u->email }} @if($deptName) · {{ $deptName }} @else · без отдела @endif</span>
                    </label>
                @empty
                    <p style="margin:0;">Нет активных пользователей.</p>
                @endforelse
            </div>
            <p id="sed-approval-order-empty" style="margin:0 0 0.5rem;font-size:0.85rem;display:none;color:#a33;">Выберите хотя бы одного согласующего.</p>
            <div class="sed-field" style="margin-bottom:0.65rem;">
                <label for="sed-approval-deadline">Срок согласования</label>
                <input type="date" class="sed-input" id="sed-approval-deadline" name="deadline" value="{{ old('deadline', $approvalDeadlineDefault) }}" min="{{ $approvalDeadlineMin }}">
            </div>
            <div class="sed-field" style="margin-bottom:0;">
                <label for="sed-initiator-comment">Комментарий</label>
                <textarea class="sed-input" id="sed-initiator-comment" name="initiator_comment" rows="3" maxlength="2000">{{ old('initiator_comment') }}</textarea>
            </div>
            <div class="sed-modal-panel__foot sed-modal-panel__foot--form" style="margin-top:0.75rem;padding-top:0.85rem;border-top:1px solid var(--border-color);">
                <a href="{{ $docBaseUrl }}" class="sed-btn sed-btn--ghost sed-btn--sm">Отмена</a>
                <button type="submit" class="sed-btn sed-btn--primary sed-btn--sm">Отправить</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('sed-approval-send');
    if (!modal) return;
    const form = document.getElementById('sed-approval-start-form');
    const searchInput = document.getElementById('sed-approval-user-search');
    const deptFilter = document.getElementById('sed-approval-dept-filter');
    const list = document.getElementById('sed-approval-user-list');
    const hiddens = document.getElementById('sed-approval-assignee-hiddens');
    const preview = document.getElementById('sed-approval-order-preview');
    const emptyHint = document.getElementById('sed-approval-order-empty');
    if (!form || !list || !hiddens || !preview) return;

    const labels = {};
    list.querySelectorAll('.sed-approval-user-cb').forEach(function (cb) {
        labels[String(cb.value)] = cb.getAttribute('data-order-line') || cb.value;
    });

    const order = [];

    function syncHiddens() {
        hiddens.innerHTML = '';
        order.forEach(function (id) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'assignees[]';
            inp.value = id;
            hiddens.appendChild(inp);
        });
    }

    function renderPreview() {
        preview.innerHTML = '';
        order.forEach(function (id, i) {
            const li = document.createElement('li');
            li.style.paddingLeft = '0';
            li.textContent = (i + 1) + '. ' + (labels[id] || id);
            preview.appendChild(li);
        });
        if (order.length === 0) {
            const li = document.createElement('li');
            li.style.listStyle = 'none';
            li.style.color = 'var(--text-muted, #666)';
            li.textContent = '—';
            preview.appendChild(li);
        }
    }

    function applyRowFilters() {
        const q = searchInput ? (searchInput.value || '').trim().toLowerCase() : '';
        const deptId = deptFilter ? deptFilter.value : '';
        list.querySelectorAll('.sed-approval-user-row').forEach(function (row) {
            const hay = (row.getAttribute('data-search') || '');
            const rowDept = row.getAttribute('data-department-id') || '';
            const matchSearch = !q || hay.indexOf(q) !== -1;
            let matchDept = true;
            if (deptId === '__none__') {
                matchDept = rowDept === '';
            } else if (deptId) {
                matchDept = rowDept === deptId;
            }
            row.style.display = (matchSearch && matchDept) ? '' : 'none';
        });
    }

    function setCheckbox(id, checked) {
        const row = list.querySelector('[data-user-id="' + id + '"]');
        if (!row) return;
        const cb = row.querySelector('.sed-approval-user-cb');
        if (cb) cb.checked = checked;
    }

    list.querySelectorAll('.sed-approval-user-cb').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const id = String(cb.value);
            if (cb.checked) {
                if (order.indexOf(id) === -1) {
                    order.push(id);
                }
            } else {
                const idx = order.indexOf(id);
                if (idx !== -1) {
                    order.splice(idx, 1);
                }
            }
            syncHiddens();
            renderPreview();
            emptyHint.style.display = 'none';
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyRowFilters);
    }
    if (deptFilter) {
        deptFilter.addEventListener('change', applyRowFilters);
    }

    form.addEventListener('submit', function (e) {
        if (order.length === 0) {
            e.preventDefault();
            emptyHint.style.display = 'block';
            emptyHint.scrollIntoView({ block: 'nearest' });
        }
    });

    const oldAssignees = @json(old('assignees', []));
    if (Array.isArray(oldAssignees) && oldAssignees.length) {
        oldAssignees.forEach(function (id) {
            const sid = String(id);
            if (order.indexOf(sid) === -1) {
                order.push(sid);
                setCheckbox(sid, true);
            }
        });
    }
    syncHiddens();
    renderPreview();
    applyRowFilters();
})();
</script>
@endif
