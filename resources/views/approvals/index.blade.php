@extends('layouts.app')

@section('title', 'Согласования — СЭД СТАВ')

@section('content')
<div class="sed-page-header">
    <h1>Согласования документов</h1>
</div>

<div class="sed-card" style="margin-bottom:1rem;">
    <h2 style="margin-top:0;">На вашем этапе</h2>
    @if($actionablePaginator->isEmpty())
        <p style="margin:0;">Нет документов на вашем этапе.</p>
    @else
        <div class="sed-table-filter-wrap" data-sed-table-filter>
            <label class="visually-hidden" for="sed-approvals-q-actionable">Поиск по таблице «На вашем этапе»</label>
            <input type="search" id="sed-approvals-q-actionable" class="sed-input sed-table-filter__input" placeholder="Поиск…" data-sed-table-filter-q autocomplete="off" style="max-width:22rem;margin-bottom:0.65rem;">
            <div class="sed-table-wrap">
                <table class="sed-table">
                    <thead>
                        <tr>
                            <th>Документ</th>
                            <th>Шаг</th>
                            <th>Срок</th>
                            <th>Инициатор</th>
                            <th style="text-align:right;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($actionablePaginator as $step)
                            @php
                                $doc = $step->process->document;
                                $deadStr = $step->process->deadline
                                    ? $step->process->deadline->timezone(config('app.timezone'))->format('d.m.Y')
                                    : '';
                                $init = $step->process->initiator;
                                $searchAction = \Illuminate\Support\Str::lower(implode(' ', array_filter([
                                    $doc?->name,
                                    $doc?->statusLabel(),
                                    (string) $step->level,
                                    'параллельно',
                                    $deadStr,
                                    $init?->displayName(),
                                    $init?->email,
                                    $init?->name,
                                ])));
                            @endphp
                            <tr
                                data-step-id="{{ $step->id }}"
                                data-approve-url="{{ route('approval-steps.approve', $step) }}"
                                data-reject-url="{{ route('approval-steps.reject', $step) }}"
                                data-search="{{ e($searchAction) }}"
                            >
                                <td>
                                    @if($doc)
                                        <a href="{{ route('documents.show', $doc) }}">{{ $doc->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $step->level }}@if($step->process->steps->where('level', $step->level)->count() > 1) (параллельно)@endif</td>
                                <td>
                                    @if($step->process->deadline)
                                        {{ $step->process->deadline->timezone(config('app.timezone'))->format('d.m.Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $step->process->initiator?->displayName() ?? '—' }}</td>
                                <td style="text-align:right;white-space:nowrap;">
                                    @if(auth()->user()->hasSignaturePin())
                                        <a href="#sed-approval-decision" class="sed-btn sed-btn--primary sed-btn--sm sed-approval-decision-trigger" data-decision="approve">Согласовать</a>
                                        <a href="#sed-approval-decision" class="sed-btn sed-btn--danger sed-btn--sm sed-approval-decision-trigger" data-decision="reject" style="margin-left:0.35rem;">Отклонить</a>
                                    @else
                                        <span>Настройте подпись</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @include('partials.pagination-prev-next', ['paginator' => $actionablePaginator, 'ariaLabel' => 'Пагинация: на вашем этапе'])
    @endif
</div>

@if(! $queuedPaginator->isEmpty())
<div class="sed-card" style="margin-bottom:1rem;">
    <h2 style="margin-top:0;">В очереди (ждут предыдущих этапов или коллег)</h2>
    <div class="sed-table-filter-wrap" data-sed-table-filter>
        <label class="visually-hidden" for="sed-approvals-q-queued">Поиск по таблице «В очереди»</label>
        <input type="search" id="sed-approvals-q-queued" class="sed-input sed-table-filter__input" placeholder="Поиск…" data-sed-table-filter-q autocomplete="off" style="max-width:22rem;margin-bottom:0.65rem;">
        <div class="sed-table-wrap">
            <table class="sed-table">
                <thead>
                    <tr>
                        <th>Документ</th>
                        <th>Ваш этап</th>
                        <th>Срок</th>
                        <th>Статус процесса</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($queuedPaginator as $step)
                        @php
                            $doc = $step->process->document;
                            $deadStr = $step->process->deadline
                                ? $step->process->deadline->timezone(config('app.timezone'))->format('d.m.Y')
                                : '';
                            $searchQueued = \Illuminate\Support\Str::lower(implode(' ', array_filter([
                                $doc?->name,
                                $doc?->statusLabel(),
                                (string) $step->level,
                                $deadStr,
                                $step->process->statusLabel(),
                            ])));
                        @endphp
                        <tr data-search="{{ e($searchQueued) }}">
                            <td>@if($doc)<a href="{{ route('documents.show', $doc) }}">{{ $doc->name }}</a>@else — @endif</td>
                            <td>{{ $step->level }}</td>
                            <td>
                                @if($step->process->deadline)
                                    {{ $step->process->deadline->timezone(config('app.timezone'))->format('d.m.Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="sed-badge sed-badge--warn">{{ $step->process->statusLabel() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('partials.pagination-prev-next', ['paginator' => $queuedPaginator, 'ariaLabel' => 'Пагинация: в очереди'])
</div>
@endif

@if(! $archivePaginator->isEmpty())
<div class="sed-card">
    <h2 style="margin-top:0;">История по вашим шагам</h2>
    <div class="sed-table-filter-wrap" data-sed-table-filter>
        <label class="visually-hidden" for="sed-approvals-q-archive">Поиск по таблице «История»</label>
        <input type="search" id="sed-approvals-q-archive" class="sed-input sed-table-filter__input" placeholder="Поиск…" data-sed-table-filter-q autocomplete="off" style="max-width:22rem;margin-bottom:0.65rem;">
        <div class="sed-table-wrap">
            <table class="sed-table">
                <thead>
                    <tr>
                        <th>Документ</th>
                        <th>Этап</th>
                        <th>Ваше решение</th>
                        <th>Комментарий</th>
                        <th>Процесс</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($archivePaginator as $step)
                        @php
                            $doc = $step->process->document;
                            $decisionRu = match (true) {
                                $step->status === 'completed' && $step->decision === 'approve' => 'согласовано',
                                $step->status === 'completed' && $step->decision === 'reject' => 'отклонено',
                                $step->status === 'cancelled' => 'отменён',
                                default => $step->status,
                            };
                            $searchArch = \Illuminate\Support\Str::lower(implode(' ', array_filter([
                                $doc?->name,
                                $doc?->statusLabel(),
                                (string) $step->level,
                                $decisionRu,
                                $step->comment,
                                $step->process->statusLabel(),
                            ])));
                        @endphp
                        <tr data-search="{{ e($searchArch) }}">
                            <td>@if($doc)<a href="{{ route('documents.show', $doc) }}">{{ $doc->name }}</a>@else — @endif</td>
                            <td>{{ $step->level }}</td>
                            <td>
                                @if($step->status === 'completed' && $step->decision === 'approve')
                                    <span class="sed-badge sed-badge--ok">согласовано</span>
                                @elseif($step->status === 'completed' && $step->decision === 'reject')
                                    <span class="sed-badge sed-badge--warn">отклонено</span>
                                @elseif($step->status === 'cancelled')
                                    <span class="sed-badge">отменён</span>
                                @else
                                    {{ $step->status }}
                                @endif
                            </td>
                            <td style="max-width:18rem;vertical-align:top;">
                                @if(filled($step->comment))
                                    <div class="sed-approval-step-comment" style="max-height:12rem;overflow-y:auto;">{!! nl2br(e($step->comment)) !!}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $step->process->statusLabel() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @include('partials.pagination-prev-next', ['paginator' => $archivePaginator, 'ariaLabel' => 'Пагинация: история'])
</div>
@endif

@php
    $approvalModalForList = session('approval_modal');
    if (empty($actionableStepIds)) {
        $approvalModalForList = null;
    } elseif ($approvalModalForList && ! in_array((int) ($approvalModalForList['step_id'] ?? 0), $actionableStepIds, true)) {
        $approvalModalForList = null;
    }
@endphp

@if(! empty($actionableStepIds) && auth()->user()->hasSignaturePin())
    @include('partials.approval-decision-modal', [
        'modalId' => 'sed-approval-decision',
        'closeHref' => route('approvals.index'),
    ])
@endif
@endsection

@section('scripts')
@if(! empty($actionableStepIds) && auth()->user()->hasSignaturePin())
    @include('partials.approval-decision-modal-script', [
        'modalId' => 'sed-approval-decision',
        'useRow' => true,
        'approvalModal' => $approvalModalForList,
    ])
@endif
<script>
(function () {
    document.querySelectorAll('[data-sed-table-filter]').forEach(function (wrap) {
        var input = wrap.querySelector('[data-sed-table-filter-q]');
        var tbody = wrap.querySelector('tbody');
        if (!input || !tbody) return;
        input.addEventListener('input', function () {
            var q = (input.value || '').trim().toLowerCase();
            tbody.querySelectorAll('tr').forEach(function (tr) {
                var hay = (tr.getAttribute('data-search') || '').toLowerCase();
                tr.style.display = (!q || hay.indexOf(q) !== -1) ? '' : 'none';
            });
        });
    });
})();
</script>
@endsection
