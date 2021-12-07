<?php

namespace CustomD\EloquentModelEncrypt\Migration;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint
{
    protected $cipher = 'AES-128-CBC';

    /**
     * Create a new encrypted string column on the table.
     *
     * @param  string  $column
     * @param  int|null  $length
     *
     * @deprecated 2.5.0 will be removed in V3, use \Illuminate\Database\Schema\Blueprint::encryptedString($column, $type) instead
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function encryptedString($column, $length = null)
    {
        $length = $length ?? Builder::$defaultStringLength;
        $length = $this->calculateEncryptionMaxCharsBase64($length);

        if ($length > 500) {
            return $this->addColumn('text', $column);
        }

        return $this->addColumn('string', $column, compact('length'));
    }

    /**
     * Create a new encrypted string column on the table. sized for a date.
     *
     * @param  string  $column
     * @param  int|null  $length
     *
     * @deprecated 2.5.0 will be removed in V3
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function encryptedDate($name, $length = 25)
    {
        return $this->encryptedString($name, $length);
    }

    /**
     * Create a new encrypted string column on the table. sized for a timestamp.
     *
     * @param  string  $column
     * @param  int|null  $length
     *
     * @deprecated 2.5.0 will be removed in V3
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function encryptedTimestamp($name, $length = 11)
    {
        return $this->encryptedString($name, $length);
    }

    /**
     * calculates the required size for teh encrypted column.
     *
     * @param int $length
     *
     * @return int
     */
    protected function calculateEncryptionMaxCharsBase64($length)
    {
        $bytes = $length * 4; // Assuming utfmb4 as a "max size"

        // Grab the block size — strings are padded to the nearest block size.
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $ivBytes = openssl_random_pseudo_bytes($ivLen);
        $block_size = strlen(openssl_encrypt('', $this->cipher, '', OPENSSL_RAW_DATA, $ivBytes));

        // Work out the maximum number of bytes when encrypted.
        // Then we work out the base64 encoded string size after encryption.
        $max_size = ceil($bytes / $block_size) * $block_size;

        return (ceil(($max_size + $ivLen) / 3) * 4) + 100;
    }
}
