<?php

namespace App\Services\Products\Filters\traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait RangeFilter
{
    protected function applyRangeFilter(Builder $query, Request $request, string $minField, string $maxField, string $dbField, ?string $relation = null): void {
        $minValue = $request->input($minField);
        $maxValue = $request->input($maxField);

        if ($minValue && $maxValue) {
            $this->applyBetweenFilter($query, $dbField, $minValue, $maxValue, $relation);
            return;
        }

        if ($minValue) {
            $this->applyMinFilter($query, $dbField, $minValue, $relation);
        }

        if ($maxValue) {
            $this->applyMaxFilter($query, $dbField, $maxValue, $relation);
        }
    }

    private function applyBetweenFilter(Builder $query, string $field, $min, $max, ?string $relation = null): void
    {
        $callback = static fn($q) => $q->whereBetween($field, [$min, $max]);
        $relation ? $query->whereHas($relation, $callback) : $callback($query);
    }

    private function applyMinFilter(Builder $query, string $field, $value, ?string $relation = null): void
    {
        $callback = static fn($q) => $q->where($field, '>=', $value);
        $relation ? $query->whereHas($relation, $callback) : $callback($query);
    }

    private function applyMaxFilter(Builder $query, string $field, $value, ?string $relation = null): void
    {
        $callback = static fn($q) => $q->where($field, '<=', $value);
        $relation ? $query->whereHas($relation, $callback) : $callback($query);
    }
}
