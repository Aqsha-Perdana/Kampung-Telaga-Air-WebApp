<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('source_channel', 30)->index(); // composer|chat
            $table->string('insight_type', 50)->index();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('metrics_json')->nullable();
            $table->json('recommendation_json')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('status', 30)->default('new')->index(); // new|reviewed|actioned|dismissed
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['source_channel', 'status']);
            $table->index(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};