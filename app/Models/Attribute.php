<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = ['name', 'type', 'options'];

    protected $casts = [
        'options' => 'json'
    ];

    public function values(): HasMany
    {
        return $this->hasMany(JobAttributeValue::class);
    }
}
