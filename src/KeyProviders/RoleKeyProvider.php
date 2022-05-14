<?php
// namespace CustomD\EloquentModelEncrypt\KeyProviders;

// use CustomD\EloquentModelEncrypt\Abstracts\KeyProvider;

// /**
//  * these methods all extend over the Eloquent methods.
//  */
// abstract class RoleKeyProvider extends KeyProvider
// {


//   //  public static $role;

//     /**
//      * Model with:
//      * role_name - primary - unique
//      * rsa_key_id - unique
//      * key - text
//      * Schema::create('keystore_role_keys', function (Blueprint $table) {
//             $table->string('role')->primary();
//             $table->unsignedBigInteger('rsa_key_id')->unique();
//             $table->text('key');
//             $table->foreign('rsa_key_id')->references('id')->on('rsa_keys')->onDelete('CASCADE')->onUpdate('RESTRICT');
//             $table->timestamps();
//         });
//      */


//      /**
//      * Should return keystore_id => public key for the ones we want!
//      *
//      * @param mixed $record
//      * @param array $extra
//      *
//      * @return array
//      */
//     public static function getPublicKeysForTable($record, $extra = []): array
//     {
//         //get all users keys from role Develoepr
//         $key = self::$model::first();

//         if (! $key) {
//             return [];
//         }

//         return [$key->rsa_key_id => $key->rsaKey->public_key];
//     }

//     /**
//      * role we are dealing with
//      *
//      * @var string
//      */
//     protected static $role = 'Staff';

//     /**
//      * UModel class for role
//      *
//      * @var string
//      */
//     protected static $model = StaffKey::class;
// }
