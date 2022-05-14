<?php
namespace CustomD\EloquentModelEncrypt\Store;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use CustomD\EloquentModelEncrypt\Contracts\PemStore;
use Illuminate\Cache\Repository;

class CachePem implements PemStore
{
    protected ?string $sessionKey = null;

    protected ?string $sessionPem = null;

    protected Repository $store;

    public function __construct(array $config = [])
    {
        $this->store = Cache::store($config['cache'] ?? config('cache.default'));
    }


    public function loadFromKey(?string $sessionKey = null): ?string
    {
        if ($sessionKey === null) {
            return;
        }

        $this->sessionKey = $sessionKey;

        if ($this->store->has($sessionKey)) {
            $this->sessionPem = decrypt($this->store->get($sessionKey));
        }

        return $this->sessionPem;
    }

    public function loadFromRequest(Request $request): ?string
    {
        $user = $request->user();

        if (! $user) {
            return;
        }

        $sessionKey = $user->id . '::' . $user->salt;

        if ($this->store->has($sessionKey)) {
            $this->storePem($sessionKey, decrypt($this->store->get($sessionKey)));
        }

        return $this->sessionPem;
    }

    public function setPem(string $sessionPem): void
    {
        $this->sessionPem = $sessionPem;
    }

    public function getPem(): ?string
    {
        return $this->sessionPem;
    }

    public function hasPem(): bool
    {
        return ! empty($this->sessionPem);
    }

    public function storePem(?string $sessionKey = null, string $privateKey, ?int $hours = null): void
    {
        $lifetime = $hours ?? (config('session.lifetime') / 60);
        $this->store->put($sessionKey, encrypt($privateKey), now()->addHours($lifetime));
        $this->sessionPem = $privateKey;
        $this->sessionKey = $sessionKey;
    }

    public function storeUserPem(User $user, string $privateKey, ?int $hours = null): void
    {
        /** @var User $user */
        $sessionKey = $user->id . '::' . $user->salt;
       // Log::debug("Storing User Session with key:" .  $sessionKey);
        $this->storePem($sessionKey, $privateKey, $hours);
    }

    public function destroy(): void
    {
        if ($this->sessionKey === null) {
            return;
        }

        $this->store->forget($this->sessionKey);
        $this->sessionPem = null;
        $this->sessionKey = null;
    }
}
