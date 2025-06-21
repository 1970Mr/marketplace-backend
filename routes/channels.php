<?php

use App\Enums\Messenger\ChatType;
use App\Models\Admin;
use App\Models\Chat;
use App\Models\Escrow;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('global.online.status', static function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('user.{id}', static function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('chat.{chatUuid}', static function ($user, $chatUuid) {
    $chatIsExists = Chat::where('uuid', $chatUuid)->where(function ($q) use ($user) {
        $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
    })->exists();

    return $chatIsExists ? ['id' => $user->id, 'name' => $user->name] : false;
});

Broadcast::channel('escrow.chat.{type}.{escrowUuid}', static function ($user, $type, $escrowUuid) {
    $type = (int)$type;
    $escrow = Escrow::where('uuid', $escrowUuid)->firstOrFail();

    $isBuyer = $user instanceof User && $type === ChatType::ESCROW_BUYER->value && $user->id === $escrow->buyer_id;
    $isSeller = $user instanceof User && $type === ChatType::ESCROW_SELLER->value && $user->id === $escrow->seller_id;
    $isDirectEscrow = $user instanceof User && $type === ChatType::DIRECT_ESCROW->value &&
                     ($user->id === $escrow->buyer_id || $user->id === $escrow->seller_id);
    $isAdmin = $user instanceof Admin && $user->id === $escrow->admin_id;

    if (!$isBuyer && !$isSeller && !$isDirectEscrow && !$isAdmin) {
        return false;
    }

    $role = $isAdmin ? 'admin' : 'user';
    $type = '';

    if ($isAdmin) {
        $type = 'admin';
    } elseif ($isBuyer || ($isDirectEscrow && $user->id === $escrow->buyer_id)) {
        $type = 'buyer';
    } elseif ($isSeller || ($isDirectEscrow && $user->id === $escrow->seller_id)) {
        $type = 'seller';
    }

    return [
        'id' => "{$role}:{$user->id}",
        'role' => $role,
        'type' => $type,
        'name' => $user->name,
        'actual_id' => $user->id
    ];
});
