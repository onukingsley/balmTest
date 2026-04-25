<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ord', function () {
    return true; // Adjust authorization logic as needed
});

Broadcast::channel('ordConfirmed{userId}', function ($user, $userId) {
    // Debug: Log to see if this is being called
    \Log::info('Authorizing channel', [
        'user_id' => $user->id,
        'requested_user_id' => (int)$userId
    ]);

    // Only allow if the authenticated user matches the channel user
    return (int) $user->id === (int) $userId;
});

Broadcast::routes();

