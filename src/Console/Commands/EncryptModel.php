<?php

namespace CustomD\EloquentModelEncrypt\Console\Commands;

use Illuminate\Console\Command;
use CustomD\EloquentModelEncrypt\Jobs\EncryptModelRecords;
use Illuminate\Support\Facades\Bus;

class EncryptModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eme:encrypt:model {model} {--c|chunk=500 : The number of records to import at a time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger encryption on your model records (skips already encrypted ones)';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $class = $this->argument('model');
        $model = new $class();

        $total = $model->withoutGlobalScopes()->whereDoesntHaveKeystore()->count();

        $this->info("Found {$total} records to encrypt");

        $last = 0;
        $chunk = $this->option('chunk');

        do {
            $records = $model->newQuery()->withoutGlobalScopes()->whereDoesntHaveKeystore()->where($model->getKeyName(), '>', $last)->take($chunk)->get();
            if ($records->isNotEmpty()) {
                EncryptModelRecords::dispatch($records);
                $last = $records->last()->getKey();
                $this->line('<comment>Queued [' . $class . '] records for encryption up to ID:</comment> ' . $last);
            }
        } while ($records->isNotEmpty());

        $this->info('All [' . $class . '] records have been queued.');
    }
}
