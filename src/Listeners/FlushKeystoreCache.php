<?php

namespace CustomD\EloquentModelEncrypt\Listeners;

use CustomD\EloquentModelEncrypt\EncryptionEngine;

class FlushKeystoreCache
{
    /**
     * Handle the event. we will make sure to clear out any cached keystores in the model encryption library
     *
     * @param  mixed  $event
     * @return void
     */
    public function handle($event)
    {
        EncryptionEngine::clearCachedKey();
    }
}
