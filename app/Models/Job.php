<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'description',
        'company_name',
        'salary_min',
        'salary_max',
        'is_remote',
        'job_type',
        'status',
        'published_at'
    ];

    protected $casts = [
        'is_remote' => 'boolean',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'published_at' => 'datetime'
    ];

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'job_language')
                    ->withPivot(['job_id', 'language_id']);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'job_location')
                    ->withPivot(['job_id', 'location_id']);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'job_category')
                    ->withPivot(['job_id', 'category_id']);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(JobAttributeValue::class, 'job_id');
    }
}
