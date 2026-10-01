<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->jsonb('criteria_ratings')->nullable()->after('rating');
            $table->unsignedTinyInteger('longevity_rating')->nullable()->change();
            $table->unsignedTinyInteger('coverage_rating')->nullable()->change();
            $table->unsignedTinyInteger('value_rating')->nullable()->change();
        });

        // Migrate existing review ratings into criteria_ratings JSONB
        DB::statement("
            UPDATE reviews 
            SET criteria_ratings = jsonb_build_object(
                'longevity', COALESCE(longevity_rating, 5),
                'coverage', COALESCE(coverage_rating, 5),
                'value', COALESCE(value_rating, 5)
            )
            WHERE criteria_ratings IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('criteria_ratings');
        });
    }
};
