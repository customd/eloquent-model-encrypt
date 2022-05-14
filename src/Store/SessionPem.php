<?php
namespace CustomD\EloquentModelEncrypt\Store;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Session;
use CustomD\EloquentModelEncrypt\Contracts\PemStore;

class SessionPem implements PemStore
{
    protected string $sessionKey;

    protected ?string $sessionPem = null;

    public function __construct(array $config = [])
    {
        $this->sessionKey = $config['session'] ?? '_cdpem_';
    }

    public function loadFromKey(?string $sessionKey = null): ?string
    {
        if (Session::has($this->sessionKey)) {
            $this->sessionPem = decrypt(Session::get($this->sessionKey));
        }
        return $this->sessionPem;
    }

    public function loadFromRequest(Request $request): ?string
    {
        if (Session::has($this->sessionKey)) {
            $this->sessionPem = decrypt(Session::get($this->sessionKey));
        }
        return $this->sessionPem;
    }

    public function setPem(?string $sessionPem = null): void
    {
        $this->sessionPem = $sessionPem;
    }

    public function getPem(): ?string
    {
        return $this->sessionPem;
    }

    public function hasPem(): bool
    {
        return $this->sessionPem !== null;
    }

    public function storePem(string $privateKey, ?string $sessionKey = null, ?int $hours = null): void
    {
        Session::put($this->sessionKey, encrypt($privateKey));
        $this->sessionPem = $privateKey;
    }

    public function storeUserPem(User $user, string $privateKey, ?int $hours = null): void
    {
        $this->storePem(privateKey: $privateKey);
    }

    public function destroy(): void
    {
        Session::forget($this->sessionKey);
        $this->sessionPem = null;
    }
}
