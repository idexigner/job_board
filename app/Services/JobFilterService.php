<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class JobFilterService
{
    protected $query;
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->query = Job::query();
        $this->filters = $filters;
    }

    public function apply(): Builder
    {
        // Generate cache key based on filters
        $cacheKey = 'jobs_filter_' . md5(json_encode($this->filters));

        // Try to get cached results IDs
        $cachedIds = Cache::remember($cacheKey, 300, function () {
            $query = Job::query();
            $this->query = $query;

            $this->applyBasicFilters()
                 ->applyRelationshipFilters()
                 ->applyEavFilters();

            return $this->query->pluck('id')->toArray();
        });

        // Create a fresh query with the cached IDs
        return Job::whereIn('id', $cachedIds);
    }

    protected function applyBasicFilters(): self
    {
        // Text/String fields with LIKE optimization
        foreach (['title', 'description', 'company_name'] as $field) {
            if (isset($this->filters[$field])) {
                $operator = $this->filters[$field]['operator'] ?? '=';
                $value = $this->filters[$field]['value'];

                if ($operator === 'OR') {
                    $this->query->where(function($query) use ($field, $value) {
                        foreach ($value as $v) {
                            $query->orWhere($field, $v);
                        }
                    });
                } else {
                    if ($operator === 'LIKE') {
                        // Optimize LIKE queries by using prefix matching when possible
                        if (strpos($value, '%') === 0) {
                            $this->query->where($field, $operator, $value);
                        } else {
                            $value = "%{$value}%";
                            $this->query->where($field, $operator, $value);
                        }
                    } else {
                        $this->query->where($field, $operator, $value);
                    }
                }
            }
        }

        // Numeric fields
        foreach (['salary_min', 'salary_max'] as $field) {
            if (isset($this->filters[$field])) {
                $filter = $this->filters[$field];
                $operator = $filter['operator'] ?? '=';
                $value = $filter['value'];

                if ($operator === 'OR') {
                    $this->query->where(function($query) use ($field, $value) {
                        foreach ($value as $v) {
                            $query->orWhere($field, $v);
                        }
                    });
                } else {
                    $this->query->where($field, $operator, $value);
                }
            }
        }

        // Boolean fields
        if (isset($this->filters['is_remote'])) {
            $filter = $this->filters['is_remote'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'];

            if ($operator === 'OR') {
                $this->query->where(function($query) use ($value) {
                    foreach ($value as $v) {
                        $query->orWhere('is_remote', $v);
                    }
                });
            } else {
                $this->query->where('is_remote', $value);
            }
        }

        // Enum fields
        if (isset($this->filters['job_type'])) {
            $filter = $this->filters['job_type'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'];

            if ($operator === 'OR') {
                $this->query->where(function($query) use ($value) {
                    foreach ($value as $v) {
                        $query->orWhere('job_type', $v);
                    }
                });
            } else {
                if (is_array($value)) {
                    $this->query->whereIn('job_type', $value);
                } else {
                    $this->query->where('job_type', $value);
                }
            }
        }

        if (isset($this->filters['status'])) {
            $filter = $this->filters['status'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'];

            if ($operator === 'OR') {
                $this->query->where(function($query) use ($value) {
                    foreach ($value as $v) {
                        $query->orWhere('status', $v);
                    }
                });
            } else {
                if (is_array($value)) {
                    $this->query->whereIn('status', $value);
                } else {
                    $this->query->where('status', $value);
                }
            }
        }

        // Date fields
        if (isset($this->filters['published_at'])) {
            $filter = $this->filters['published_at'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'];

            if ($operator === 'OR') {
                $this->query->where(function($query) use ($value) {
                    foreach ($value as $v) {
                        $query->orWhere('published_at', $v);
                    }
                });
            } else {
                $this->query->where('published_at', $operator, $value);
            }
        }

        return $this;
    }

    protected function applyRelationshipFilters(): self
    {
        // Languages filter
        if (isset($this->filters['languages'])) {
            $this->handleRelationshipFilter('languages');
        }

        // Locations filter
        if (isset($this->filters['locations'])) {
            $this->handleRelationshipFilter('locations', 'city');
        }

        // Categories filter
        if (isset($this->filters['categories'])) {
            $this->handleRelationshipFilter('categories');
        }

        return $this;
    }

    protected function handleRelationshipFilter(string $relation, string $field = 'name'): void
    {
        $filter = $this->filters[$relation];
        $operator = $filter['operator'] ?? 'HAS_ANY';
        $values = Arr::wrap($filter['value']);


        switch ($operator) {
            case 'HAS_ANY':
                $this->query->whereHas($relation, function ($query) use ($values, $field) {
                    $query->whereIn($field, $values);
                });
                break;

            case 'IS_ANY':
                $this->query->whereHas($relation, function ($query) use ($relation,$values, $field) {
                    $query->whereIn($relation . '.' . $field, $values);
                });
                break;

            case 'EXISTS':
                $this->query->whereHas($relation);
                break;

            case '=':
                $this->query->whereHas($relation, function ($query) use ($values, $field) {
                    $query->whereIn($field, $values);
                }, '=', count($values));
                break;
        }
    }

    protected function applyEavFilters(): self
    {
        if (!isset($this->filters['attributes'])) {
            return $this;
        }

        // Group attributes by type for better performance
        $attributeFilters = collect($this->filters['attributes'])->groupBy(function ($filter) {
            return $filter['operator'];
        });

        foreach ($attributeFilters as $operator => $filters) {
            $this->query->where(function ($query) use ($operator, $filters) {
                foreach ($filters as $attributeName => $filter) {
                    $value = $filter['value'];

                    $query->whereHas('attributeValues', function ($q) use ($attributeName, $operator, $value) {
                        $q->whereHas('attribute', function ($q) use ($attributeName) {
                            $q->where('name', $attributeName);
                        });

                        if (is_array($value)) {
                            $q->whereIn('value', $value);
                        } else {
                            $q->where('value', $operator, $value);
                        }
                    });
                }
            });
        }

        return $this;
    }
}
