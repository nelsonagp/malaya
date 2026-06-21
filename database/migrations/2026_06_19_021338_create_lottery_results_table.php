<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_results', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('lottery_id')->constrained('lotteries')->cascadeOnDelete();
            $table->date('draw_date');
            $table->integer('draw_number')->nullable();
            $table->jsonb('numbers');
            $table->jsonb('prize_breakdown')->nullable();
            $table->decimal('jackpot_amount', 15, 2)->nullable();
            $table->string('currency', 10)->default('COP');
            $table->boolean('is_verified')->default(false);
            $table->text('source_url')->nullable();
            $table->jsonb('raw_data')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['lottery_id', 'draw_date']);
            $table->index(['lottery_id', 'draw_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_results');
    }
};
