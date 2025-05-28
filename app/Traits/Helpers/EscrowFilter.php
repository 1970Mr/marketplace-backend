<?php

namespace App\Traits\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait EscrowFilter
{
    public function scopeFilterByProductTitle(Builder $query, string|null $search): Builder
    {
        return $query->when($search, function ($query) use ($search) {
            $query->whereHas('offer.product', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        });
    }

    public function scopeFilterBy(Builder $query, string $column, string|int|null $value): Builder
    {
        return $query->when($value, function ($query) use ($column, $value) {
            $query->where($column, $value);
        });
    }

    public function scopeFilterByDateRange(Builder $query, ?string $fromDate, ?string $toDate): Builder
    {
        return $query->when($fromDate || $toDate, function ($query) use ($fromDate, $toDate) {
            if ($fromDate && $toDate) {
                $start = Carbon::parse($fromDate)->startOfDay();
                $end = Carbon::parse($toDate)->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($fromDate) {
                $query->where('created_at', '>=', Carbon::parse($fromDate)->startOfDay());
            } elseif ($toDate) {
                $query->where('created_at', '<=', Carbon::parse($toDate)->endOfDay());
            }
        });
    }
}
