<?php
namespace CustomD\EloquentModelEncrypt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static ?string loadFromKey(?string $sessionKey = null)
 * @method static ?string loadFromRequest(Request $request)
 * @method static void setPem(string $sessionPem)
 * @method static ?string getPem()
 * @method static bool hasPem()
 * @method static void storePem(?string $sessionKey = null, string $privateKey, ?int $hours = null)
 * @method static void storeUserPem(\Illuminate\Foundation\Auth\User $user, string $privateKey, ?int $hours = null)
 * @method static void destroy()
 */
class PemStore extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cd-pem-store';
    }
}