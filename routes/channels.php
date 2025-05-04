<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', static function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatUuid}', static function ($user, $chatUuid) {
    $chatIsExists =  Chat::where('uuid', $chatUuid)->where(function($q) use ($user) {
            $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
        })->exists();

    return $chatIsExists ? ['id' => $user->id, 'name' => $user->name] : false;
});
