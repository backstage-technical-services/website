<?php

namespace App;

use App\Models\Log;
use App\Models\Users\User;

abstract class Logger
{
    /**
     * Write an entry to the log.
     *
     * @param string                 $action
     * @param bool                   $status
     * @param mixed                  $payload
     * @param \App\Models\Users\User $user
     *
     * @return void
     */
    public static function log($action, $status = true, $payload = null, User $user = null)
    {
        Log::create([
            'user_id' => $user === null ? (auth()->check() ? auth()->user()->id : null) : $user->id,
            'ip_address' => request()->ip(),
            'action' => $action,
            'payload' => is_null($payload) ? null : json_encode($payload),
            'status' => (bool) $status,
        ]);
    }
}
