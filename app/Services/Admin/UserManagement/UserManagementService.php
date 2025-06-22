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
        $query = User::query();

        // Apply search
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        // Apply filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply future filters
        $this->applyFutureFilters($query, $filters);

        // Apply Pagination
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 10;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getUserChats(User $user): Collection
    {
        return Chat::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with([
                'buyer:id,name,email',
                'seller:id,name,email',
                'messages' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function applyFutureFilters($query, array $filters)
    {
        // TODO: Implement future filters here
    }
}
