<?php

namespace CustomD\EloquentModelEncrypt\Observers;

use Illuminate\Support\Facades\DB;

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
     * @param \Illuminate\Database\Eloquent\Model&\CustomD\EloquentModelEncrypt\Contracts\Encryptable $model
     */
    public function creating($model)
    {
        $model->getEncryptionEngine()->assignSynchronousKey();
        $model->mapEncryptedValues();
    }

    /**
     * Updating event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model&\CustomD\EloquentModelEncrypt\Contracts\Encryptable $model
     */
    public function updating($model)
    {
        if ($model->isUpdatingEncryptedFields()) {
            // Let's assign a sync key for this record (if necessary), and encrypt the fields that are set.
            $model->assignRecordsSynchronousKey(true);
            $model->storeKeyReferences();
            $model->mapEncryptedValues();
        }
    }

    /**
     * Created event called from the Model.
     *
     * @param \Illuminate\Database\Eloquent\Model&\CustomD\EloquentModelEncrypt\Contracts\Encryptable $model
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
     * @param \Illuminate\Database\Eloquent\Model&\CustomD\EloquentModelEncrypt\Contracts\Encryptable $model
     */
    public function deleted($model)
    {
        //only remove if fully trashing
        if (! method_exists($model, 'isForceDeleting') || $model->isForceDeleting()) {
            $recs = $model->getKeystoreModel()->where('table', $model->getTableKeystoreReference())->where('ref', $model->getKey());
            $recs->delete();
        }
    }
}
