<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotteries', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country', 100)->default('Colombia');
            $table->char('country_code', 2)->default('CO');
            $table->text('logo_url')->nullable();
            $table->text('website_url')->nullable();
            $table->text('results_url')->nullable();
            $table->string('scraper_class')->nullable();
            $table->jsonb('scraper_config')->nullable();
            $table->jsonb('draw_schedule')->nullable();
            $table->string('draw_frequency', 50)->nullable();
            $table->integer('number_count')->default(4);
            $table->integer('number_range_min')->default(0);
            $table->integer('number_range_max')->default(9999);
            $table->boolean('has_series')->default(false);
            $table->boolean('has_fractions')->default(false);
            $table->text('prize_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_scraped_at')->nullable();
            $table->text('scrape_error')->nullable();
            $table->text('affiliate_url')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotteries');
    }
};
