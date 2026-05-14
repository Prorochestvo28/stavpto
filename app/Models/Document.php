<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'status',
        'author_id',
        'last_edited_by',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version_number');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany('version_number');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'document_department');
    }

    public function approvalProcesses(): HasMany
    {
        return $this->hasMany(ApprovalProcess::class, 'document_id')->orderByDesc('id');
    }

    public function activeApprovalProcess(): HasOne
    {
        return $this->hasOne(ApprovalProcess::class, 'document_id')
            ->where('status', ApprovalProcess::STATUS_IN_PROGRESS)
            ->latestOfMany('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class)->orderBy('created_at');
    }

    /** Подпись статуса документа для интерфейса (русский). */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Черновик',
            'review' => 'На согласовании',
            'approved' => 'Согласован',
            'rejected' => 'Отклонён',
            default => $this->status,
        };
    }
}