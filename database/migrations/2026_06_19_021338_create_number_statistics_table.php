<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('number_statistics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('lottery_id')->constrained('lotteries')->cascadeOnDelete();
            $table->string('number', 20);
            $table->integer('total_appearances')->default(0);
            $table->date('last_appeared_date')->nullable();
            $table->integer('days_since_last_appearance')->nullable();
            $table->decimal('appearance_frequency', 5, 4)->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['lottery_id', 'number']);
            $table->index(['lottery_id', 'total_appearances']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_statistics');
    }
};
