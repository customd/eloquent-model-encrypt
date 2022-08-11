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

    public function __construct(public Collection $records)
    {
    }

    public function handle(): void
    {
        $this->records->each(function ($record) {
            //as this is queued - might hit here after being edited by the user (0.00001% chance but hey.)
            if ($record->recordKeystore === null) {
            //we got here, now lets force save
                $record->forceEncrypt();
            }
        });
    }
}
