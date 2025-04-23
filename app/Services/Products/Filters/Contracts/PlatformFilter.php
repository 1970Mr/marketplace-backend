<?php

namespace App\Services\Products\Filters\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface PlatformFilter
{
    public function apply(Builder $query, Request $request): void;
}
