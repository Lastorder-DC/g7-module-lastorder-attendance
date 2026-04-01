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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('attendance_date');
            $table->time('attendance_time');
            $table->string('greeting', 200);
            $table->integer('base_point');
            $table->integer('random_point')->default(0);
            $table->integer('total_point');
            $table->unsignedSmallInteger('daily_rank')->nullable();
            $table->unsignedInteger('consecutive_days')->default(1);
            $table->unsignedInteger('total_days')->default(1);
            $table->string('ip_address', 45);
            $table->boolean('is_auto')->default(false);
            $table->timestamps();

            // 하루 1회 출석 보장
            $table->unique(['user_id', 'attendance_date']);
            // 일별 순위 조회
            $table->index(['attendance_date', 'daily_rank']);
            // 일별 출석 시각순 조회
            $table->index(['attendance_date', 'created_at']);
            // 사용자별 연속 출석 조회
            $table->index(['user_id', 'consecutive_days']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
