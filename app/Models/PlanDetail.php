<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'plan_id'])]
class PlanDetail extends Model
{
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function realizations(): HasMany
    {
        return $this->hasMany(Realization::class);
    }
}
