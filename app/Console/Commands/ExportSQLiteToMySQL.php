<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportSQLiteToMySQL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-sqlite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a MySQL-compatible SQL dump from local SQLite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating MySQL-compatible dump...');

        $tables = DB::connection('sqlite')
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

        $sql = "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->name;
            
            if ($tableName === 'migrations') {
                continue;
            }

            $this->info("Processing table: {$tableName}");

            $sql .= "-- Table: {$tableName}\n";
            $sql .= "TRUNCATE TABLE `{$tableName}`;\n";

            $data = DB::connection('sqlite')->table($tableName)->get();

            if ($data->isEmpty()) {
                $sql .= "-- No data\n\n";
                continue;
            }

            foreach ($data as $row) {
                $columns = [];
                $values = [];

                foreach ((array) $row as $col => $val) {
                    $columns[] = "`$col`";
                    
                    if (is_null($val)) {
                        $values[] = "NULL";
                    } elseif (is_numeric($val)) {
                        $values[] = $val;
                    } else {
                        // Basic MySQL escaping
                        $val = str_replace(["\\", "'"], ["\\\\", "''"], $val);
                        $values[] = "'$val'";
                    }
                }

                $sql .= "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents('cloudsql_import.sql', $sql);

        $this->info('Dump file created: cloudsql_import.sql');
        $this->info('You can now upload this file to Google Cloud Storage and import it via the Cloud SQL Console.');
    }
}
