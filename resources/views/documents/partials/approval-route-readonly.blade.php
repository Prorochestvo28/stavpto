@if($process->initiator_comment)
    <p class="sed-approval-route__init">
        <strong>Комментарий инициатора.</strong><br>
        {!! nl2br(e($process->initiator_comment)) !!}
    </p>
@endif
<ol class="sed-approval-route__levels">
    @foreach($process->steps->groupBy('level') as $level => $stepsAtLevel)
        <li class="sed-approval-route__level">
            <strong>Этап {{ $level }}</strong>@if($stepsAtLevel->count() > 1) <span class="sed-approval-route__note">(параллельно)</span>@endif
            <ul class="sed-approval-route__people">
                @foreach($stepsAtLevel as $st)
                    <li>
                        {{ $st->assignee?->displayName() ?? $st->assignee?->email ?? '—' }}
                        @if($st->assignee?->department)
                            · {{ $st->assignee->department->name }}
                        @endif
                        —
                        @if($st->status === 'pending')
                            <span class="sed-badge sed-badge--warn">ожидает</span>
                        @elseif($st->status === 'completed' && $st->decision === 'approve')
                            <span class="sed-badge sed-badge--ok">согласовано</span>
                        @elseif($st->status === 'completed' && $st->decision === 'reject')
                            <span class="sed-badge sed-badge--warn">отклонено</span>
                        @elseif($st->status === 'cancelled')
                            <span class="sed-badge">отменён</span>
                        @endif
                        @if(filled($st->comment) && $st->status === 'completed')
                            <div class="sed-approval-route__comment">{!! nl2br(e($st->comment)) !!}</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </li>
    @endforeach
</ol>
