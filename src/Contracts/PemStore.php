<?php
namespace CustomD\EloquentModelEncrypt\Contracts;

use Illuminate\Http\Request;

interface PemStore
{
    public function loadFromKey(?string $sessionKey = null): ?string;
    public function loadFromRequest(Request $request): ?string;
    public function setPem(string $sessionPem): void;
    public function getPem(): ?string;
    public function hasPem(): bool;
    public function storePem(?string $sessionKey = null, string $privateKey, ?int $hours = null): void;
    public function destroy(): void;
}
