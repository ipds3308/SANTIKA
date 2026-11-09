<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.dashboard', function (User $user): bool {
    return in_array(strtolower((string) $user->role), ['admin', 'cs'], true);
});
