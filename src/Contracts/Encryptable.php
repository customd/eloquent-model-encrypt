<?php
namespace CustomD\EloquentModelEncrypt\Contracts;

interface Encryptable
{
    /**
     * @var array<mixed, mixed> $options
     */
    public function forceEncrypt(array $options = []);

    public function getEncryptionEngine();

    public function isEncryptable(string $key): bool;

    public function isValueEncrypted(?string $value): bool;

    public function getTableKeystoreReference(): string;

}
