<?php
namespace CustomD\EloquentModelEncrypt\Model\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use CustomD\EloquentModelEncrypt\Model\RsaKey;
use CustomD\EloquentModelEncrypt\Facades\PemStore;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

trait HasUserKeystore
{
    public static function bootHasUserKeystore(): void
    {
        static::creating(function (Model $model) {
            if ($model->isPasswordHashed($model->password)) {
                    throw new EncryptException("Password should not be pre-hashed");
            }
            $cleartextPassword = $model->password;
            $model->addKeyPair($cleartextPassword);
            $model->password = $model->hashPassword($cleartextPassword);
        });

        static::updating(function ($model) {

            if ($model->isDirty('password')) {
                if ($model->isPasswordHashed($model->password)) {
                    throw new EncryptException("Password should not be pre-hashed");
                }

                $privateKey = PemStore::getPem();

                if (! $privateKey) {
                    throw new EncryptException("Current Private Key is not available");
                }

                $cleartextPassword = $model->password;
                $model->password = $model->hashPassword($cleartextPassword);

                $rsa = EloquentAsyncKeys::setPrivateKey($privateKey)
                    ->setPublicKey($model->rsaKey->public_key);
                $rsa->setNewPassword($cleartextPassword, $model->salt);
                $model->rsaKey->private_key = $rsa->getPrivateKey();
                $model->rsaKey->save();
            }
        });
    }

    public function hashPassword(string $cleartextPassword): string
    {
        return Hash::make($cleartextPassword);
    }

    /**
     * Gets the RSA Keystore (pub.private) key for the user
     *
     */
    public function rsaKey(): BelongsTo
    {
        return $this->belongsTo(RsaKey::class, 'rsa_key_id');
    }

    /**
     * Creates the users RsaKeypair
     */
    public function addKeyPair(string $password, bool $force = false): self
    {
        if ($this->rsa_key_id && ! $force) {
            throw new EncryptException("Cannot add Keypair as one already exists (or set the force option)");
        }

        if ($this->isPasswordHashed($password)) {
            throw new EncryptException("Password should not be pre-hashed");
        }

        $this->salt = Str::random(16);

        $rsa = EloquentAsyncKeys::reset()->setPassword($password)
            ->setSalt($this->salt)->create();

        $keystore = RsaKey::create([
            'public_key'  => $rsa->getPublicKey(),
            'private_key' => $rsa->getPrivateKey(),
        ]);

        $this->rsa_key_id = $keystore->id;

        return $this;
    }

    public function getDecryptedPrivateKey(string $password): ?string
    {
        return EloquentAsyncKeys::setKeys(
            null,
            $this->rsaKey->private_key,
            $password,
            $this->salt
        )->getDecryptedPrivateKey();
    }


    protected function isPasswordHashed(string $password): bool
    {
        return \strlen($password) === 60 && preg_match('/^\$2y\$/', $password);
    }
}
