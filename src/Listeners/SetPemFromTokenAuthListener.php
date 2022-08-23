<?php

namespace CustomD\EloquentModelEncrypt\Listeners;

use Laravel\Sanctum\Events\TokenAuthenticated;

class SetPemFromTokenAuthListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\TokenAuthenticated  $event
     * @return void
     */
    public function handle(TokenAuthenticated $event)
    {
        $event->token->tokenable->withAccessToken(
            $event->token
        )->loadPemToken();
    }
}
