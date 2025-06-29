<?php

namespace App\Services\Admin\Dashboard;

use App\Enums\Escrow\DirectEscrowStage;
use App\Enums\Escrow\DirectEscrowStatus;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use App\Enums\Products\ProductStatus;
use App\Models\Admin;
use App\Models\Escrow;
use App\Models\Message;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Builder;

readonly class AdminDashboardService
{
    public function getDashboardData(Admin $admin): array
    {
        return [
            'admin_name' => $admin->name,
            'new_escrows' => $this->getNewEscrowsData(),
            'your_escrows' => $this->getYourEscrowsData($admin),
            'need_response' => $this->getNeedResponseData($admin),
            'payment_check' => $this->getPaymentCheckData($admin),
            'new_listings' => $this->getNewListingsData(),
            'recent_new_escrows' => $this->getRecentNewEscrows(),
            'recent_your_escrows' => $this->getRecentYourEscrows($admin),
        ];
    }

    private function getNewEscrowsData(): array
    {
        $adminEscrows = $this->countUnassignedAdminEscrows();
        $directEscrows = $this->countUnassignedDirectEscrows();

        return [
            'admin' => $adminEscrows,
            'direct' => $directEscrows,
            'total' => $adminEscrows + $directEscrows,
        ];
    }

    private function countUnassignedAdminEscrows(): int
    {
        return Escrow::whereNull('admin_id')
            ->whereHas('adminEscrow', function (Builder $query) {
                $query->where('status', EscrowStatus::PENDING->value);
            })->count();
    }

    private function countUnassignedDirectEscrows(): int
    {
        return Escrow::whereNull('admin_id')
            ->whereHas('directEscrow', function (Builder $query) {
                $query->where('status', DirectEscrowStatus::PENDING->value);
            })->count();
    }

    private function getYourEscrowsData(Admin $admin): array
    {
        $adminEscrows = $this->countAssignedAdminEscrows($admin);
        $directEscrows = $this->countAssignedDirectEscrows($admin);

        return [
            'admin' => $adminEscrows,
            'direct' => $directEscrows,
            'total' => $adminEscrows + $directEscrows,
        ];
    }

    private function countAssignedAdminEscrows(Admin $admin): int
    {
        return Escrow::where('admin_id', $admin->id)
            ->whereHas('adminEscrow', function (Builder $query) {
                $query->whereIn('status', [
                    EscrowStatus::PENDING->value,
                    EscrowStatus::ACTIVE->value
                ]);
            })->count();
    }

    private function countAssignedDirectEscrows(Admin $admin): int
    {
        return Escrow::where('admin_id', $admin->id)
            ->whereHas('directEscrow', function (Builder $query) {
                $query->whereIn('status', [
                    DirectEscrowStatus::PENDING->value,
                    DirectEscrowStatus::ACTIVE->value,
                    DirectEscrowStatus::DISPUTED->value
                ]);
            })->count();
    }

    private function getNeedResponseData(Admin $admin): array
    {
        $adminEscrows = $this->countAdminEscrowsWithUnreadMessages($admin);
        $directEscrows = $this->countDirectEscrowsWithUnreadMessages($admin);

        return [
            'admin' => $adminEscrows,
            'direct' => $directEscrows,
            'total' => $adminEscrows + $directEscrows,
        ];
    }

    private function countAdminEscrowsWithUnreadMessages(Admin $admin): int
    {
        return Escrow::where('admin_id', $admin->id)
            ->whereHas('adminEscrow', function (Builder $query) {
                $query->whereIn('status', [
                    EscrowStatus::PENDING->value,
                    EscrowStatus::ACTIVE->value
                ]);
            })
            ->where(function (Builder $query) use ($admin) {
                $query->whereHas('buyerChat.messages', function (Builder $subQuery) use ($admin) {
                    $subQuery->where('sender_id', '!=', $admin->id)
                        ->whereNull('read_at');
                })
                    ->orWhereHas('sellerChat.messages', function (Builder $subQuery) use ($admin) {
                        $subQuery->where('sender_id', '!=', $admin->id)
                            ->whereNull('read_at');
                    });
            })
            ->count();
    }

    private function countDirectEscrowsWithUnreadMessages(Admin $admin): int
    {
        return Escrow::where('admin_id', $admin->id)
            ->whereHas('directEscrow', function (Builder $query) {
                $query->whereIn('status', [
                    DirectEscrowStatus::PENDING->value,
                    DirectEscrowStatus::ACTIVE->value,
                    DirectEscrowStatus::DISPUTED->value
                ]);
            })
            ->whereHas('directChat.messages', function (Builder $query) use ($admin) {
                $query->where('sender_id', '!=', $admin->id)
                    ->whereNull('read_at');
            })
            ->count();
    }

    private function getPaymentCheckData(Admin $admin): array
    {
        $adminEscrows = $this->countAdminEscrowsWithPaymentUploaded($admin);
        $directEscrows = $this->countDirectEscrowsWithPaymentUploaded($admin);

        return [
            'admin' => $adminEscrows,
            'direct' => $directEscrows,
            'total' => $adminEscrows + $directEscrows,
        ];
    }

    private function countAdminEscrowsWithPaymentUploaded(Admin $admin): int
    {
        return Escrow::where('admin_id', $admin->id)
            ->whereHas('adminEscrow', function (Builder $query) {
                $query->where('stage', EscrowStage::PAYMENT_UPLOADED->value);
            })
            ->count();
    }

    private function countDirectEscrowsWithPaymentUploaded(Admin $admin): int
    {
        return Escrow::where('admin_id', $admin->id)
            ->whereHas('directEscrow', function (Builder $query) {
                $query->where('stage', DirectEscrowStage::PAYMENT_UPLOADED->value);
            })
            ->count();
    }

    private function getNewListingsData(): array
    {
        $pendingProducts = Product::where('status', ProductStatus::PENDING->value)
            ->where('is_completed', true)
            ->count();

        return [
            'total' => $pendingProducts,
        ];
    }

    private function getRecentNewEscrows(): array
    {
        $escrows = Escrow::whereNull('admin_id')
            ->with(['offer.product', 'adminEscrow', 'directEscrow'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($escrow) {
                return [
                    'id' => $escrow->id,
                    'uuid' => $escrow->uuid,
                    'product_title' => $escrow->offer->product->title ?? 'Unknown Product',
                    'product_type' => $escrow->offer->product->type ?? 'Unknown',
                    'type' => $escrow->type->value,
                    'type_label' => $escrow->type->label(),
                    'created_at' => $escrow->created_at,
                ];
            });

        return $escrows->toArray();
    }

    private function getRecentYourEscrows(Admin $admin): array
    {
        $escrows = Escrow::where('admin_id', $admin->id)
            ->with(['offer.product', 'adminEscrow', 'directEscrow', 'buyerChat', 'sellerChat', 'directChat'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($escrow) use ($admin) {
                $hasUnreadMessages = $this->checkEscrowUnreadMessages($escrow, $admin);

                return [
                    'id' => $escrow->id,
                    'uuid' => $escrow->uuid,
                    'product_title' => $escrow->offer->product->title ?? 'Unknown Product',
                    'type' => $escrow->type->value,
                    'type_label' => $escrow->type->label(),
                    'created_at' => $escrow->created_at,
                    'has_unread_messages' => $hasUnreadMessages,
                ];
            });

        return $escrows->toArray();
    }

    private function checkEscrowUnreadMessages(Escrow $escrow, Admin $admin): bool
    {
        if ($escrow->isDirectEscrow()) {
            $chat = $escrow->directChat;
        } else {
            $chat = $escrow->buyerChat ?: $escrow->sellerChat;
        }

        if (!$chat) {
            return false;
        }

        return Message::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $admin->id)
            ->whereNull('read_at')
            ->exists();
    }
}
