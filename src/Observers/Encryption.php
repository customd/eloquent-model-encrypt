<?php

namespace CustomD\EloquentModelEncrypt\Observers;

use Illuminate\Support\Facades\DB;
use CustomD\EloquentModelEncrypt\Model\Keystore;

class Encryption
{
    /**
     * Saving event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function saving($model)
    {
        DB::beginTransaction();
    }

    /**
     * Creating event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function creating($model)
    {
        $model->getEncryptionEngine()->assignSynchronousKey();
        $model->mapEncryptedValues();
    }

    /**
     * Updating event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function updating($model)
    {
        // Editing a record, lets get the sync key for this record and encrypt the fields that are set.
        if (! $model->getEncryptionEngine()->getSynchronousKey()) {
            $model->getEncryptionEngine()->assignSynchronousKey();
            $model->storeKeyReferences();
        }
        $model->mapEncryptedValues();
    }

    /**
     * Created event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function created($model)
    {
        $model->storeKeyReferences();
    }

    /**
     * Saved event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function saved($model)
    {
        DB::commit();
    }

    /**
     * Retrieved event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function retrieved($model)
    {
        //assign the key to allow for decryption
        try {
            $key = $model->getPrivateKeyForRecord();
            $model->getEncryptionEngine()->assignSynchronousKey($key);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
            \Log::debug('Did not find a key for ' . $model->getTable());
            // Do nothig for now
            // could be we have some items that are not already enctypted
    // (ie encrypted added in after the records where created)
        }
    }

    /**
     * Deleted event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function deleted($model)
    {
        //only remove if fully trashing
        if (! method_exists($model, 'isForceDeleting') || $model->isForceDeleting()) {
            $recs = Keystore::where('table', $model->getTable())->where('ref', $model->id);
            $recs->delete();
        }
    }
}
