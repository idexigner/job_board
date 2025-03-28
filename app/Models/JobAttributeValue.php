<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAttributeValue extends Model
{
    protected $fillable = ['job_id', 'attribute_id', 'value'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
