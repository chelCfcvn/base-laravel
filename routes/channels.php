<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat-room', function () {
    return true;
});
