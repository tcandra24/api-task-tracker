<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['plan_id', 'plan_detail_id', 'description', 'user_id', 'progress'])]
class Realization extends Model
{
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function planDetail(): BelongsTo
    {
        return $this->belongsTo(PlanDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
