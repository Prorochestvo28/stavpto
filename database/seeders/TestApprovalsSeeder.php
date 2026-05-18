<?php

namespace Database\Seeders;

use App\Models\ApprovalProcess;
use App\Models\Document;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestApprovalsSeeder extends Seeder
{
    public function run(): void
    {
        $initiator = User::query()->where('email', 'prorokov_2019@mail.ru')->first()
            ?? User::query()->where('email', 'admin@stav.ltd')->first();

        $head = User::query()->where('email', 'prorokov_2019@mail.ru')->first();
        $legal = User::query()->where('email', 'ivanova_legal@mail.ru')->first();
        $hr = User::query()->where('email', 'petrov_hr@mail.ru')->first();
        $accounting = User::query()->where('email', 'sidorova_buh@mail.ru')->first();

        if (! $initiator || ! $head || ! $legal || ! $hr || ! $accounting) {
            return;
        }

        foreach ([$head, $legal, $hr, $accounting, $initiator] as $user) {
            if (! $user->signature_pin) {
                $user->update(['signature_pin' => Hash::make('1234')]);
            }
        }

        $service = app(ApprovalWorkflowService::class);

        $scenarios = [
            [
                'document' => 'Договор подряда №205',
                'levels' => [[$head->id]],
                'setup' => fn (ApprovalProcess $process) => null,
            ],
            [
                'document' => 'Положение об оплате труда',
                'levels' => [[$head->id], [$hr->id]],
                'setup' => function (ApprovalProcess $process) use ($head): void {
                    $this->completeStep($process, $head, 'Согласовано начальником');
                },
            ],
            [
                'document' => 'Инструкция по охране труда',
                'levels' => [[$head->id], [$hr->id]],
                'setup' => function (ApprovalProcess $process) use ($head, $hr): void {
                    $this->completeStep($process, $head, 'Ок');
                    $this->completeStep($process, $hr, 'Согласовано');
                    $process->update([
                        'status' => ApprovalProcess::STATUS_COMPLETED,
                        'end_date' => now(),
                    ]);
                    $process->document()->update(['status' => 'approved']);
                },
            ],
            [
                'document' => 'Протокол совещания 12.03',
                'levels' => [[$head->id]],
                'setup' => function (ApprovalProcess $process) use ($head): void {
                    $step = $process->steps()->where('assignee_id', $head->id)->first();
                    if ($step) {
                        $step->update([
                            'status' => 'completed',
                            'decision' => 'reject',
                            'comment' => 'Нужны правки в приложении.',
                            'completed_at' => now(),
                        ]);
                    }
                    $process->steps()->where('status', 'pending')->update([
                        'status' => 'cancelled',
                        'completed_at' => now(),
                    ]);
                    $process->update([
                        'status' => ApprovalProcess::STATUS_REJECTED,
                        'end_date' => now(),
                    ]);
                    $process->document()->update(['status' => 'rejected']);
                },
            ],
            [
                'document' => 'Договор ГПХ с Ивановой',
                'levels' => [[$legal->id, $head->id]],
                'setup' => function (ApprovalProcess $process) use ($legal): void {
                    $this->completeStep($process, $legal, 'Юрист согласовал');
                },
            ],
            [
                'document' => 'План закупок 2026',
                'levels' => [[$legal->id]],
                'setup' => fn (ApprovalProcess $process) => null,
            ],
            [
                'document' => 'Отчёт о расходах',
                'levels' => [[$accounting->id], [$head->id]],
                'setup' => function (ApprovalProcess $process) use ($accounting): void {
                    $this->completeStep($process, $accounting, 'Цифры проверены');
                },
            ],
        ];

        foreach ($scenarios as $scenario) {
            $document = Document::query()->where('name', $scenario['document'])->first();
            if (! $document || $document->approvalProcesses()->exists()) {
                continue;
            }

            $document->update(['status' => 'draft']);

            $process = $service->startProcess(
                $document,
                $initiator,
                $scenario['levels'],
                'Тестовый маршрут (сидер)',
                Carbon::now()->addDays(14)
            );

            ($scenario['setup'])($process);
        }
    }

    private function completeStep(ApprovalProcess $process, User $assignee, ?string $comment = null): void
    {
        $step = $process->steps()
            ->where('assignee_id', $assignee->id)
            ->where('status', 'pending')
            ->orderBy('level')
            ->first();

        if (! $step) {
            return;
        }

        $step->update([
            'status' => 'completed',
            'decision' => 'approve',
            'comment' => $comment,
            'completed_at' => now(),
        ]);
    }
}
