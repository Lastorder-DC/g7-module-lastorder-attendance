<?php

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
        Schema::create('attendance_bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->string('bonus_type', 20);
            $table->integer('bonus_point');
            $table->date('bonus_date');
            $table->string('description', 200);
            $table->timestamp('created_at')->nullable();

            // 사용자별 보너스 조회
            $table->index(['user_id', 'bonus_date']);
            // 일별 보너스 현황
            $table->index(['bonus_date', 'bonus_type']);
            // 중복 보너스 방지
            $table->unique(['user_id', 'bonus_date', 'bonus_type']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_bonuses');
    }
};
