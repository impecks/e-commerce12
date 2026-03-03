<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferSQLiteToMySQL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:transfer-sqlite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer all data from local SQLite to configured MySQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->confirm('This will truncate all tables in the MySQL database. Do you want to continue?')) {
            return;
        }

        $this->info('Starting data transfer...');

        // Disable foreign key checks for MySQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = DB::connection('sqlite')
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        foreach ($tables as $table) {
            $tableName = $table->name;
            
            if ($tableName === 'migrations') {
                continue;
            }

            $this->info("Transferring table: {$tableName}");

            // Clear MySQL table
            DB::table($tableName)->truncate();

            // Get data from SQLite
            $data = DB::connection('sqlite')->table($tableName)->get();

            if ($data->isEmpty()) {
                continue;
            }

            // Chunk insert for performance and memory
            $chunks = $data->chunk(100);
            
            foreach ($chunks as $chunk) {
                // Convert stdClass to array
                $records = $chunk->map(function ($item) {
                    return (array) $item;
                })->toArray();

                DB::table($tableName)->insert($records);
            }
        }

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Data transfer completed successfully!');
    }
}
