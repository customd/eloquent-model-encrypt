<?php

namespace CustomD\EloquentModelEncrypt\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class EncryptModelRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Collection $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function handle()
    {
        $this->records->each(function ($record) {
            //we got here, now lets force save
            $record->forceEncrypt();
        });
    }
}
