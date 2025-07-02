<?php

namespace App\Services\Messenger;

use App\Enums\Messenger\ChatType;
use App\Models\Admin;
use App\Models\Escrow;
use App\Models\User;

class ChatAuthorizationService
{
    public static function authorizeEscrowChat($user, int $chatType, Escrow $escrow): bool
    {
        return self::isBuyer($user, $chatType, $escrow) ||
               self::isSeller($user, $chatType, $escrow) ||
               self::isDirectEscrowParticipant($user, $chatType, $escrow) ||
               self::isAssignedAdmin($user, $escrow);
    }

    private static function isBuyer($user, int $chatType, Escrow $escrow): bool
    {
        return $user instanceof User &&
               $chatType === ChatType::ESCROW_BUYER->value &&
               $user->id === $escrow->buyer_id;
    }

    private static function isSeller($user, int $chatType, Escrow $escrow): bool
    {
        return $user instanceof User &&
               $chatType === ChatType::ESCROW_SELLER->value &&
               $user->id === $escrow->seller_id;
    }

    private static function isDirectEscrowParticipant($user, int $chatType, Escrow $escrow): bool
    {
        return $user instanceof User &&
               $chatType === ChatType::DIRECT_ESCROW->value &&
               ($user->id === $escrow->buyer_id || $user->id === $escrow->seller_id);
    }

    private static function isAssignedAdmin($user, Escrow $escrow): bool
    {
        return $user instanceof Admin && $user->id === $escrow->admin_id;
    }
}
