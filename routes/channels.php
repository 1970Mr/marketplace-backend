<?php

use App\Models\Chat;
use App\Models\Escrow;
use App\Services\Messenger\ChatAuthorizationService;
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

    if (!ChatAuthorizationService::authorizeEscrowChat($user, $type, $escrow)) {
        return false;
    }

    return [
        'id' => $user->getAuthIdentifierForBroadcasting(),
        'name' => $user->name
    ];
});
