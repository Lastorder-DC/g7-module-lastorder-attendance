# 02. 데이터베이스 설계

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 01-OVERVIEW](01-OVERVIEW.md)

---

## 1. 테이블 설계

### 1.1 `attendances` 테이블

출석 기록을 저장하는 메인 테이블.

| 컬럼 | 타입 | Nullable | 설명 |
|------|------|----------|------|
| `id` | bigint unsigned | NO | PK, auto_increment |
| `user_id` | bigint unsigned | NO | 회원 ID (users.id FK) |
| `attended_at` | date | NO | 출석 날짜 (YYYY-MM-DD) |
| `attended_time` | time | NO | 출석 시각 (HH:MM:SS) |
| `greeting` | varchar(200) | YES | 인삿말 |
| `base_point` | int | NO | 기본 포인트 (default: 0) |
| `bonus_point` | int | NO | 보너스 포인트 합계 (default: 0) |
| `random_point` | int | NO | 랜덤 보너스 포인트 (default: 0) |
| `rank_point` | int | NO | 순위 보너스 포인트 (default: 0) |
| `consecutive_point` | int | NO | 연속출석 보너스 포인트 (default: 0) |
| `total_point` | int | NO | 총 지급 포인트 (default: 0) |
| `daily_rank` | int unsigned | NO | 당일 출석 순위 (default: 0) |
| `consecutive_days` | int unsigned | NO | 출석 시점 연속출석 일수 (default: 1) |
| `total_days` | int unsigned | NO | 출석 시점 총 출석 일수 (default: 1) |
| `ip_address` | varchar(45) | YES | IP 주소 (IPv6 대응) |
| `is_auto` | boolean | NO | 자동출석 여부 (default: false) |
| `created_at` | timestamp | YES | 생성 시간 |
| `updated_at` | timestamp | YES | 수정 시간 |

#### 인덱스

| 인덱스명 | 컬럼 | 유형 | 용도 |
|----------|------|------|------|
| `attendances_user_attended_unique` | `user_id, attended_at` | UNIQUE | 1일 1회 출석 보장 |
| `attendances_attended_at_index` | `attended_at` | INDEX | 날짜별 조회 |
| `attendances_user_id_index` | `user_id` | INDEX | 회원별 조회 |
| `attendances_attended_at_daily_rank_index` | `attended_at, daily_rank` | INDEX | 순위 조회 |
| `attendances_user_attended_at_index` | `user_id, attended_at` | INDEX | 캘린더 조회 |

---

## 2. 마이그레이션

### 규칙 (AGENTS.md 준수)

- 한국어 `comment()` 필수
- `down()` 구현 필수
- Laravel Blueprint 사용

```php
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
```

---

## 3. Eloquent 모델

```php
<?php

namespace Modules\Lastorder\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attended_at',
        'attended_time',
        'greeting',
        'base_point',
        'bonus_point',
        'random_point',
        'rank_point',
        'consecutive_point',
        'total_point',
        'daily_rank',
        'consecutive_days',
        'total_days',
        'ip_address',
        'is_auto',
    ];

    protected $casts = [
        'attended_at' => 'date',
        'base_point' => 'integer',
        'bonus_point' => 'integer',
        'random_point' => 'integer',
        'rank_point' => 'integer',
        'consecutive_point' => 'integer',
        'total_point' => 'integer',
        'daily_rank' => 'integer',
        'consecutive_days' => 'integer',
        'total_days' => 'integer',
        'is_auto' => 'boolean',
    ];

    /**
     * 출석한 회원
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
```

---

## 4. 설계 의도

### 왜 단일 테이블인가?

- 출석 기록은 단순한 일대일 관계 (1일 1회원 1행)
- 연속출석, 순위 등의 파생 데이터는 출석 시점에 스냅샷으로 저장
- 별도의 통계 테이블 없이도 효율적 조회 가능 (인덱스 활용)

### 포인트 컬럼 분리 이유

| 컬럼 | 계산 시점 | 용도 |
|------|-----------|------|
| `base_point` | 출석 시 | 기본 출석 포인트 |
| `random_point` | 출석 시 | 랜덤 보너스 (확률 기반) |
| `rank_point` | 출석 시 | 1~3위 순위 보너스 |
| `consecutive_point` | 출석 시 | 연속출석(7/30/365일) 보너스 |
| `bonus_point` | 출석 시 | random + rank + consecutive 합계 |
| `total_point` | 출석 시 | base + bonus 합계 |

포인트를 분리 저장하면 사용자 페이지에서 각 항목별 내역을 표시할 수 있고, 관리자가 통계를 볼 때도 유용함.

### `consecutive_days`, `total_days` 스냅샷 저장 이유

- 출석 시점의 연속일수와 총일수를 기록해두면, 매번 재계산할 필요 없음
- 목록 조회 시 JOIN이나 서브쿼리 없이 바로 표시 가능
- 참고 이미지의 "개근" 컬럼과 "총 출석일" 컬럼에 직접 대응

---

## 다음: [03-BACKEND-ARCHITECTURE.md](03-BACKEND-ARCHITECTURE.md) →
