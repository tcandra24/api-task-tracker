<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['realization_id', 'file', 'type'])]
class Attachment extends Model
{
    public function realization(): BelongsTo
    {
        return $this->belongsTo(Realization::class);
    }
}
