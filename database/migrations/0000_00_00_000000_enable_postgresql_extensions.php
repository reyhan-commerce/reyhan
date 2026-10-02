<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "cube";');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "earthdistance";');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS "earthdistance";');
        DB::statement('DROP EXTENSION IF EXISTS "cube";');
        DB::statement('DROP EXTENSION IF EXISTS "pg_trgm";');
    }
};
