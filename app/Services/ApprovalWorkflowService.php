<?php

namespace App\Services;

use App\Models\ApprovalProcess;
use App\Models\ApprovalStep;
use App\Models\Document;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    /**
     * @param  list<list<int>>  $levels  каждый внутренний массив — параллельные согласующие на одном этапе
     */
    public function startProcess(Document $document, User $initiator, array $levels, ?string $comment = null, ?DateTimeInterface $deadline = null): ApprovalProcess
    {
        if ($document->status !== 'draft') {
            throw ValidationException::withMessages([
                'stages' => ['Запустить согласование можно только для документа в статусе «Черновик».'],
            ]);
        }

        if ($document->activeApprovalProcess()->exists()) {
            throw ValidationException::withMessages([
                'stages' => ['По этому документу уже идёт согласование.'],
            ]);
        }

        if ($levels === []) {
            throw ValidationException::withMessages([
                'stages' => ['Добавьте хотя бы один этап согласования.'],
            ]);
        }

        foreach ($levels as $idx => $assigneeIds) {
            $assigneeIds = array_values(array_unique(array_map('intval', $assigneeIds)));
            if ($assigneeIds === []) {
                throw ValidationException::withMessages([
                    "stages.{$idx}" => ['На каждом этапе выберите хотя бы одного согласующего.'],
                ]);
            }
        }

        $deadlineAt = $deadline !== null
            ? Carbon::parse($deadline)
            : now()->addWeek();

        return DB::transaction(function () use ($document, $initiator, $levels, $comment, $deadlineAt) {
            $process = ApprovalProcess::query()->create([
                'document_id' => $document->id,
                'name' => 'Согласование: '.$document->name,
                'status' => ApprovalProcess::STATUS_IN_PROGRESS,
                'initiator_id' => $initiator->id,
                'start_date' => now(),
                'deadline' => $deadlineAt,
                'initiator_comment' => $comment,
            ]);

            $stepNumber = 1;
            foreach ($levels as $levelIndex => $assigneeIds) {
                $level = $levelIndex + 1;
                $assigneeIds = array_values(array_unique(array_map('intval', $assigneeIds)));
                foreach ($assigneeIds as $userId) {
                    ApprovalStep::query()->create([
                        'process_id' => $process->id,
                        'level' => $level,
                        'step_number' => $stepNumber++,
                        'assignee_id' => $userId,
                        'status' => 'pending',
                        'assigned_at' => now(),
                    ]);
                }
            }

            $document->update(['status' => 'review']);

            return $process->load('steps.assignee');
        });
    }

    public function approve(ApprovalStep $step, User $actor, string $signaturePin, ?string $comment = null): void
    {
        DB::transaction(function () use ($step, $actor, $signaturePin, $comment) {
            $step = ApprovalStep::query()->lockForUpdate()->findOrFail($step->id);
            $process = ApprovalProcess::query()->lockForUpdate()->findOrFail($step->process_id);
            $process->load('steps');

            if (! $actor->verifySignaturePin($signaturePin)) {
                throw ValidationException::withMessages([
                    'signature_pin' => ['Неверная электронная подпись.'],
                ]);
            }

            if (! $step->isPending()) {
                throw ValidationException::withMessages([
                    'step' => ['Этот шаг уже обработан.'],
                ]);
            }

            if ((int) $step->assignee_id !== (int) $actor->id) {
                abort(403);
            }

            $current = $process->currentLevel();
            if ($current === null || (int) $step->level !== (int) $current) {
                throw ValidationException::withMessages([
                    'step' => ['Сейчас не ваш этап согласования.'],
                ]);
            }

            $step->update([
                'status' => 'completed',
                'decision' => 'approve',
                'comment' => $comment,
                'completed_at' => now(),
            ]);

            $process->refresh()->load('steps');

            if ($this->allStepsApproved($process)) {
                $process->update([
                    'status' => ApprovalProcess::STATUS_COMPLETED,
                    'end_date' => now(),
                ]);
                $process->document()->update(['status' => 'approved']);
            }
        });
    }

    public function reject(ApprovalStep $step, User $actor, string $signaturePin, ?string $comment = null): void
    {
        DB::transaction(function () use ($step, $actor, $signaturePin, $comment) {
            $step = ApprovalStep::query()->lockForUpdate()->findOrFail($step->id);
            $process = ApprovalProcess::query()->lockForUpdate()->findOrFail($step->process_id);
            $process->load('steps');

            if (! $actor->verifySignaturePin($signaturePin)) {
                throw ValidationException::withMessages([
                    'signature_pin' => ['Неверная электронная подпись.'],
                ]);
            }

            if (! $step->isPending()) {
                throw ValidationException::withMessages([
                    'step' => ['Этот шаг уже обработан.'],
                ]);
            }

            if ((int) $step->assignee_id !== (int) $actor->id) {
                abort(403);
            }

            $current = $process->currentLevel();
            if ($current === null || (int) $step->level !== (int) $current) {
                throw ValidationException::withMessages([
                    'step' => ['Сейчас не ваш этап согласования.'],
                ]);
            }

            $step->update([
                'status' => 'completed',
                'decision' => 'reject',
                'comment' => $comment,
                'completed_at' => now(),
            ]);

            $process->steps()
                ->where('id', '!=', $step->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'completed_at' => now(),
                ]);

            $process->update([
                'status' => ApprovalProcess::STATUS_REJECTED,
                'end_date' => now(),
            ]);

            $process->document()->update(['status' => 'rejected']);
        });
    }

    private function allStepsApproved(ApprovalProcess $process): bool
    {
        return $process->steps->every(
            fn (ApprovalStep $s) => $s->status === 'completed' && $s->decision === 'approve'
        );
    }
}
