<?php
namespace CustomD\EloquentModelEncrypt\Store;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;
use CustomD\EloquentModelEncrypt\Contracts\PemStore;
use Illuminate\Contracts\Auth\Authenticatable;

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
            return null;
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
            return null;
        }

        // @phpstan-ignore-next-line -- user salt will be on the user table due to migration
        $sessionKey = $user->id . '::' . $user->salt;

        if ($this->store->has($sessionKey)) {
            $this->storePem(
                privateKey: decrypt($this->store->get($sessionKey)),
                sessionKey: $sessionKey,
            );
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

    public function storePem(string $privateKey, ?string $sessionKey = null, ?int $hours = null): void
    {
        $lifetime = $hours ?? (config('session.lifetime') / 60);
        $this->store->put($sessionKey, encrypt($privateKey), now()->addHours($lifetime));
        $this->sessionPem = $privateKey;
        $this->sessionKey = $sessionKey;
    }

    public function storeUserPem(Authenticatable $user, string $privateKey, ?int $hours = null): void
    {
        /** @var User $user */
        // @phpstan-ignore-next-line -- user salt will be on the user table due to migration
        $sessionKey = $user->id . '::' . $user->salt;

        $this->storePem(
            privateKey: $privateKey,
            sessionKey: $sessionKey,
            hours: $hours,
        );
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

    public function extend(?int $hours = null): void
    {
        if ($this->sessionPem === null || $this->sessionKey === null) {
            //nothing to extend here
            return;
        }
        $lifetime = $hours ?? (config('session.lifetime') / 60);
        $this->store->put($this->sessionKey, encrypt($this->sessionPem), now()->addHours($lifetime));
      }
}
