<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalProcess extends Model
{
    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'document_id',
        'name',
        'status',
        'initiator_id',
        'start_date',
        'end_date',
        'deadline',
        'initiator_comment',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'deadline' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'process_id')->orderBy('level')->orderBy('id');
    }

    /**
     * Номер активного этапа (одинаковый level = параллельное согласование).
     * null — процесс не в работе или все этапы пройдены.
     */
    public function currentLevel(): ?int
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            return null;
        }

        $levels = $this->steps->pluck('level')->unique()->sort()->values();
        foreach ($levels as $lvl) {
            $atLevel = $this->steps->where('level', $lvl);
            if ($atLevel->contains(fn (ApprovalStep $s) => $s->decision === 'reject')) {
                return null;
            }
            $allApproved = $atLevel->every(fn (ApprovalStep $s) => $s->status === 'completed' && $s->decision === 'approve');
            if ($allApproved) {
                continue;
            }

            return (int) $lvl;
        }

        return null;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /** Подпись статуса процесса согласования для интерфейса (русский). */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_COMPLETED => 'Завершён',
            self::STATUS_REJECTED => 'Отклонён',
            self::STATUS_CANCELLED => 'Отменён',
            default => $this->status,
        };
    }
}
