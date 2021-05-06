# User Key Example

As an example setup here is how you could implement a User Keystore for each user to use their own keystore:

## 1. Create Keystore Class
In `app\KeyProviders` add a new class `UserKeyProvider.php` with the following content:

```php
<?php

namespace App\KeyProviders;

use App\Models\User;
use App\Services\SessionKey;
use Illuminate\Support\Facades\Log;
use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserKeyProvider extends KeyProvider
{

    public static function getPublicKeysForTable($record, $extra = []): array
    {
        $user_ids = collect(self::getRecordsUserIds($record, $extra = []))->filter()->unique();

        if ($user_ids->count() === 0) {
            return [];
        }

        $users = User::whereIn('user_id', $user_ids)->get();

        $publicKeys = [];

        foreach ($users as $user) {
            $publicKeys[$user->rsaKey->id] = $user->rsaKey->public_key;
        }

        return $publicKeys;
    }

    public static function getPrivateKeyForRecord(string $table, int $recordId): ?string
    {

        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $rsa_keystore_id = $user->rsaKey->id;

        $rec = self::getKeyFromKeystore($table, $recordId, $rsa_keystore_id);

        if ($rec === null) {
            return false;
        }

        try {
            if (! SessionKey::hasPem()) {
                throw new \Exception('User Should not be able to be logged in without a PEM');
            }

            $pem = SessionKey::getPem();
        } catch (\Exception $exception) {
            // On an error, we need to revoke the token, the session etc. full logout of this user.
            Log::critical($exception->getMessage(), ['user_id' => $user->id]);
            $user->token()->revoke();
            SessionKey::destroy();
            throw new UnauthorizedHttpException('Token', 'User Should not be able to be logged in without a PEM');
        }

        return EloquentAsyncKeys::reset()->decryptWithKey($pem, $rec->key, $rec->Keystores->first()->key);
    }


    protected static function getRecordsUserIds($record, $extra = []): array
    {

        // Is there an method to make sure to get the correct user id(s) from the record
        if (method_exists($record, 'getUserKeyProviderIds')) {
            return $record->getUserKeyProviderIds();
        }

        // or does this record have a related user
        if (! empty($record->user)) {
            return [$record->user->id];
        }

        // or lastly is there a user>
        $user = auth()->user();
        return $user ? [$user->id] : [];
    }
}
```

**A few notes here:**
- Because multiple users could need access to a record, you can pass one or more public keys back , you can decide how to get your public keys based on the record, for this example you can see we added a getRecordUserIds method that checks for a few options on the current record.
- Getting our private key you can see a reference to SessionKey, this could be stored in session / or as we have done it using a speciallised redis store that is linked to the user (as we are using passport and do not have sessions).

## 2. Extend the users model

Either in the, model or add a trait to your users model basically as below (one advantage of this example, is that it implements automatic password hashing, similar to Laravel Fortify). You could alternatively implement this in Laravel Fortify's actions, or whatever auth package you're using.

```php
<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use App\Services\SessionKey;
use Illuminate\Support\Facades\Hash;
use App\Actions\User\UpdatePrivateKeyPasswordAction;
use CustomD\EloquentModelEncrypt\Model\RsaKey;
use Illuminate\Contracts\Encryption\EncryptException;
use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys;

trait UserKeystore
{
    public static function bootUserKeystore()
    {
        static::creating(function ($model) {

            $cleartextPassword = $model->password;
            $model->addKeyPair($cleartextPassword);
            $model->password = Hash::make($cleartextPassword);
        });

        static::updating(function ($model) {

            if ($model->isDirty('password')) {
                if ($model->isPasswordHashed($model->password)) {
                    throw new EncryptException("Password should not be pre-hashed");
                }

                $privateKey = SessionKey::getPrivateKey();

                if (! $privateKey) {
                    throw new EncryptException("Current Private Key is not available");
                }

                $cleartextPassword = $model->password;
                $model->password = Hash::make($cleartextPassword);

                (new UpdatePrivateKeyPasswordAction($model))->execute($cleartextPassword, $privateKey);
            }
        });
    }


    protected function isPasswordHashed(string $password): bool
    {
        return \strlen($password) === 60 && preg_match('/^\$2y\$/', $password);
    }


    /**
     * Gets the RSA Keystore (pub.private) key for the user
     *
     * @return ?RsaKey
     */
    public function rsaKey()
    {
        return $this->belongsTo(RsaKey::class, 'rsa_key_id');
    }

    /**
     * Creates the users RsaKeypair
     */
    public function addKeyPair(string $password, $force = false): self
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

    public function getDecryptedPrivateKey(string $password)
    {
        return EloquentAsyncKeys::setKeys(
            null,
            $this->rsaKey->private_key,
            $password,
            $this->salt
        )->getDecryptedPrivateKey();
    }
}
```

As you can see it hooks into the creating event on the user model to add the users keypair and encrypt it with the password, as well as the updating password (shown below is the action).

```php
<?php

namespace App\Actions\User;

use CustomD\EloquentAsyncKeys\Facades\EloquentAsyncKeys as FacadesEloquentAsyncKeys;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UpdatePrivateKeyPasswordAction
{

    protected Authenticatable $user;


    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }

    public function execute($password, string $privateKey)
    {

        $rsa = FacadesEloquentAsyncKeys::setPrivateKey($privateKey)
        ->setPublicKey($this->user->rsaKey->public_key);
        $rsa->setNewPassword($password, $this->user->salt);
        $this->user->rsaKey->private_key = $rsa->getPrivateKey();
        return $this->user->rsaKey->save();
    }
}
```

Finally on your login controller (or by extending the login controller from whatever auth package you are using) add the following method where you can set how you want to store the users decrypted private key for the duration of their session.

```php
    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        SessionKey::storePrivateKey($user->id, $user->getDecryptedPrivateKey($request->get('password')));
    }
```
