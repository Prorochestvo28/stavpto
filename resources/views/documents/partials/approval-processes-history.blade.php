@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\ApprovalProcess> $pastApprovalProcesses */
    $tz = config('app.timezone');
@endphp
<section class="sed-card sed-approval-history-doc" aria-labelledby="sed-approval-past-heading">
    <h2 id="sed-approval-past-heading" class="sed-approval-history-doc__title">История согласований</h2>
    @foreach($pastApprovalProcesses as $p)
        <div class="sed-approval-history-doc__entry">
            <p class="sed-approval-history-doc__line">
                <strong>{{ $p->start_date?->timezone($tz)->format('d.m.Y H:i') ?? '—' }}</strong>
                — {{ $p->statusLabel() }}
            </p>
            <p class="sed-approval-history-doc__meta">
                Инициатор: {{ $p->initiator?->displayName() ?? $p->initiator?->email ?? '—' }}
                @if($p->deadline)
                    · Срок: {{ $p->deadline->timezone($tz)->format('d.m.Y') }}
                @endif
                @if($p->end_date)
                    · Завершён: {{ $p->end_date->timezone($tz)->format('d.m.Y H:i') }}
                @endif
            </p>
            <div class="sed-approval-history-doc__route">
                @include('documents.partials.approval-route-readonly', ['process' => $p])
            </div>
        </div>
    @endforeach
</section>
