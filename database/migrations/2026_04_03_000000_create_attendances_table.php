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

            $table->unsignedBigInteger('user_id')
                ->comment('회원 ID');

            $table->date('attended_at')
                ->comment('출석 날짜');

            $table->time('attended_time')
                ->comment('출석 시각');

            $table->string('greeting', 200)
                ->nullable()
                ->comment('인삿말');

            $table->integer('base_point')
                ->default(0)
                ->comment('기본 포인트');

            $table->integer('bonus_point')
                ->default(0)
                ->comment('보너스 포인트 합계');

            $table->integer('random_point')
                ->default(0)
                ->comment('랜덤 보너스 포인트');

            $table->integer('rank_point')
                ->default(0)
                ->comment('순위 보너스 포인트');

            $table->integer('consecutive_point')
                ->default(0)
                ->comment('연속출석 보너스 포인트');

            $table->integer('total_point')
                ->default(0)
                ->comment('총 지급 포인트');

            $table->unsignedInteger('daily_rank')
                ->default(0)
                ->comment('당일 출석 순위');

            $table->unsignedInteger('consecutive_days')
                ->default(1)
                ->comment('출석 시점 연속출석 일수');

            $table->unsignedInteger('total_days')
                ->default(1)
                ->comment('출석 시점 총 출석 일수');

            $table->string('ip_address', 45)
                ->nullable()
                ->comment('IP 주소');

            $table->boolean('is_auto')
                ->default(false)
                ->comment('자동출석 여부');

            $table->timestamps();

            // 인덱스
            $table->unique(['user_id', 'attended_at'], 'attendances_user_attended_unique');
            $table->index('attended_at', 'attendances_attended_at_index');
            $table->index('user_id', 'attendances_user_id_index');
            $table->index(['attended_at', 'daily_rank'], 'attendances_attended_at_daily_rank_index');

            // 외래키
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
