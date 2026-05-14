<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    protected $fillable = [
        'process_id',
        'level',
        'step_number',
        'assignee_id',
        'status',
        'decision',
        'comment',
        'assigned_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ApprovalProcess::class, 'process_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActionableBy(User $user): bool
    {
        if (! $this->isPending() || (int) $this->assignee_id !== (int) $user->id) {
            return false;
        }

        $process = $this->relationLoaded('process')
            ? $this->process
            : $this->process()->with('steps')->first();

        if (! $process || ! $process->isInProgress()) {
            return false;
        }

        if (! $process->relationLoaded('steps')) {
            $process->load('steps');
        }

        $current = $process->currentLevel();

        return $current !== null && (int) $this->level === (int) $current;
    }
}
