<?php

namespace CustomD\EloquentModelEncrypt\Observers;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use CustomD\EloquentModelEncrypt\Model\Keystore;

class Encryption
{
    /**
     * Saving event called from the Model.
     */
    public function saving()
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
        $model->assignRecordsSynchronousKey(true);
        $model->storeKeyReferences();
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
     */
    public function saved()
    {
        DB::commit();
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
            $recs = $model->getKeystoreModel()::where('table', $model->getTableKeystoreReference())->where('ref', $model->getKey());
            $recs->delete();
        }
    }
}
