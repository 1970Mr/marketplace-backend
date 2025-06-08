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
    $escrow = Escrow::where('uuid', $escrowUuid)->firstOrFail();

    // Determine role and validate membership
    $role = null;
    if ($user instanceof User) {
        if ((int)$type === ChatType::ESCROW_BUYER->value && $user->id === $escrow->buyer_id) {
            $role = 'user';
        } elseif ((int)$type === ChatType::ESCROW_SELLER->value && $user->id === $escrow->seller_id) {
            $role = 'user';
        }
    } elseif ($user instanceof Admin && $user->id === $escrow->admin_id) {
        $role = 'admin';
    }

    if (!$role) {
        return false;
    }

    // Prefix the ID so 'user:1' and 'admin:1' never collide
    $prefixedId = "{$role}:{$user->id}";

    return [
        'id' => $prefixedId,
        'type' => $role,
        'name' => $user->name,
    ];
});
