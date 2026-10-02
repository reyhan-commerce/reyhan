<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('province_id')->constrained('provinces')->restrictOnDelete();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_mobile', 15);
            $table->string('postal_code', 10);
            $table->text('address_line');
            $table->string('building_number', 20)->nullable();
            $table->string('unit', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
