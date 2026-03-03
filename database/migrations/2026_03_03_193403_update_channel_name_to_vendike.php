<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('channel_translations')
            ->where('name', 'Velocity')
            ->orWhere('name', 'Default')
            ->update(['name' => 'Vendike']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('channel_translations')
            ->where('name', 'Vendike')
            ->update(['name' => 'Default']);
    }
};
