<?php
namespace CustomD\EloquentModelEncrypt\Contracts;

use Illuminate\Database\Eloquent\Model;

interface UserKeystoreContract
{
    public function hashPassword(string $cleartextPassword): string;

    public function rsaKey(): BelongsTo;

    public function addKeyPair(string $password, bool $force = false): static;

    public function getDecryptedPrivateKey(string $password): ?string;

}
