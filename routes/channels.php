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
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatUuid}', static function ($user, $chatUuid) {
    $chatIsExists =  Chat::where('uuid', $chatUuid)->where(function($q) use ($user) {
            $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
        })->exists();

    return $chatIsExists ? ['id' => $user->id, 'name' => $user->name] : false;
});

Broadcast::channel('escrow.chat.{type}.{escrowId}', static function ($user, $type, $escrowId) {
    $escrow = Escrow::findOrFail($escrowId);

    if ($user instanceof User) {
        return ($type === ChatType::ESCROW_BUYER->value && $user->id === $escrow->buyer_id) ||
            ($type === ChatType::ESCROW_SELLER->value && $user->id === $escrow->seller_id);
    }

    if ($user instanceof Admin) {
        return $user->id === $escrow->admin_id;
    }

    return false;
});
