<?php
namespace CustomD\EloquentModelEncrypt\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface UserKeystoreContract
{
    public function hashPassword(string $cleartextPassword): string;

    public function rsaKey(): BelongsTo;

    public function addKeyPair(string $password, bool $force = false): static;

    public function getDecryptedPrivateKey(string $password): ?string;

}
