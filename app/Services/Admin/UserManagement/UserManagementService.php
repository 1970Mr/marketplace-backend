<?php

namespace App\Services\Admin\UserManagement;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserManagementService
{
    public function getFilteredUsers(array $filters): LengthAwarePaginator
    {
        $query = User::withCount(['products', 'escrowsAsBuyer', 'escrowsAsSeller']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('country', 'like', "%{$filters['country']}%");
            });
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['products_count'])) {
            $this->applyCountRangeFilter($query, 'products_count', $filters['products_count']);
        }

        if (!empty($filters['escrows_count'])) {
            $this->applyCountRangeFilter($query, '(escrows_as_buyer_count + escrows_as_seller_count)', $filters['escrows_count']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function getUserChats(User $user): Collection
    {
        return Chat::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with([
                'buyer:id,name,email',
                'seller:id,name,email',
                'admin:id,name,email',
                'product:title',
                'messages'
            ])
            ->latest()
            ->get();
    }

    private function applyCountRangeFilter($query, string $column, string $range): void
    {
        switch ($range) {
            case '1-3':
                $query->havingRaw("{$column} >= 1")->havingRaw("{$column} <= 3");
                break;
            case '3-5':
                $query->havingRaw("{$column} >= 3")->havingRaw("{$column} <= 5");
                break;
            case '5-10':
                $query->havingRaw("{$column} >= 5")->havingRaw("{$column} <= 10");
                break;
            case '10+':
                $query->havingRaw("{$column} > 10");
                break;
        }
    }

    public function getUserWithRelations(User $user): User
    {
        $relations = [
            'adminEscrow',
            'directEscrow',
            'offer.product',
            'buyer',
            'seller'
        ];

        return $user->load([
            'escrowsAsBuyer' => fn($q) => $q->with($relations),
            'escrowsAsSeller' => fn($q) => $q->with($relations)
        ])
            ->loadCount(['products', 'escrowsAsBuyer', 'escrowsAsSeller']);
    }
}
