<?php
namespace CustomD\EloquentModelEncrypt\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Attempting;
use CustomD\EloquentModelEncrypt\Facades\PemStore;

class UserEventSubscriber
{
    protected static ?Attempting $attempt = null;

    /**
     * Handling our authentication attempt, we store the request here temporarily.
     *
     * @param \Illuminate\Auth\Events\Attempting $attempt
     */
    public function handleAuthenticationAttempt(Attempting $attempt): void
    {
        self::$attempt = $attempt;
    }

    /**
     * Handle the event.
     */
    public function handleUserLogin(Login $event): void
    {
        if (self::$attempt === null) {
            return;
        }
        // @phpstan-ignore-next-line
        $pem = $event->user->getDecryptedPrivateKey(self::$attempt->credentials['password']);

        PemStore::storeUserPem($event->user, $pem, config('session.lifetime') / 60);
    }

    public function handleUserLogout(): void
    {
        PemStore::destroy();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events): void
    {
        $events->listen(
            Attempting::class,
            [static::class, 'handleAuthenticationAttempt']
        );

        $events->listen(
            Login::class,
            [static::class, 'handleUserLogin']
        );

        $events->listen(
            Logout::class,
            [static::class, 'handleUserLogout']
        );
    }
}
