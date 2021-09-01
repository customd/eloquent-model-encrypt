<?php

namespace CustomD\EloquentModelEncrypt\Console\Commands;

use Illuminate\Console\Command;
use CustomD\EloquentModelEncrypt\Jobs\EncryptModelRecords;

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
     *
     * @return int
     */
    public function handle()
    {
        $class = $this->argument('model');
        $model = new $class();

        $total = $model->withoutGlobalScopes()->count();

        $this->info("Found {$total} records to encrypt");

        $skip = 0;
        $chunk = $this->option('chunk');

        while ($skip < $total) {
            $records = $model->newQuery()->withoutGlobalScopes()->skip($skip)->take($chunk)->get();
            if ($records->isNotEmpty()) {
                EncryptModelRecords::dispatch($records);
                $key = $records->last()->getKey();
                $this->line('<comment>Queued [' . $class . '] records for encryption up to ID:</comment> ' . $key);
            }
            $skip += $chunk;
        }
        $this->info('All [' . $class . '] records have been queued.');
    }
}
