<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description',
        'head_user_id',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_department');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }
}
