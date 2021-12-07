<?php

namespace CustomD\EloquentModelEncrypt\Concerns;

use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Abstracts\Engine;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface Encryptable
{
    public static function bootHasEncryption(): void;

    public function getEncryptionEngine(): Engine;

    public function isEncryptable(string $key): bool;

    public function isValueEncrypted(?string $value): bool;

    public function getTableKeystoreReference(): string;

    public function forceEncrypt(array $options = []): bool;

    public function mapEncryptedValues(): void;

    public function assignRecordsSynchronousKey(bool $create = false): void;

    public function storeKeyReferences(): void;

    public function getKeystoreKeyModel(): Model;

    public function getKeystoreModel(): Model;
}
