<?php
namespace CustomD\EloquentModelEncrypt\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Encryptable
{
    public function forceEncrypt(): void;

    public function getEncryptionEngine();

    public function isEncryptable(string $key): bool;

    public function isCyphertext(?string $value): bool;

    public function isPlaintext(?string $value): bool;

    public function getTableKeystoreReference(): string;

    public function mapEncryptedValues(): void;

    public function assignRecordsSynchronousKey(bool $create = false): void;

    public function storeKeyReferences(): void;

    public function getKeystoreKeyModel(): Model;

    public function getKeystoreModel(): Model;

    public function getPrivateKeyForRecord(): string;

    public function isUpdatingEncryptedFields(): bool;
}
