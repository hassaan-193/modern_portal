<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('portal-notifications', function ($user) {
    return !is_null($user);
});

Broadcast::channel('purchase-orders', function ($user) {
    return !is_null($user);
});

Broadcast::channel('attendance', function ($user) {
    return !is_null($user);
});

Broadcast::channel('payments', function ($user) {
    return !is_null($user);
});

Broadcast::channel('memos', function ($user) {
    return !is_null($user);
});
