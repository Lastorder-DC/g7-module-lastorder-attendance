## 3. 데이터베이스 설계

### 3.1 `attendances` 테이블 (출석 기록)

매일 출석 체크 기록을 저장하는 핵심 테이블.

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | `bigint unsigned` PK | 자동 증가 ID |
| `user_id` | `bigint unsigned` FK | 회원 ID (users 테이블 참조) |
| `attendance_date` | `date` | 출석 날짜 |
| `attendance_time` | `time` | 출석 시각 |
| `greeting` | `varchar(200)` | 인삿말 |
| `base_point` | `int` | 기본 출석 포인트 |
| `random_point` | `int` default 0 | 랜덤 추가 포인트 |
| `total_point` | `int` | 총 획득 포인트 (base + random) |
| `daily_rank` | `smallint unsigned` NULL | 당일 출석 순위 |
| `consecutive_days` | `int unsigned` default 1 | 연속 출석 일수 |
| `total_days` | `int unsigned` default 1 | 총 출석 일수 |
| `ip_address` | `varchar(45)` | 출석 IP 주소 |
| `is_auto` | `boolean` default false | 자동출석 여부 |
| `created_at` | `timestamp` | 생성일시 |
| `updated_at` | `timestamp` | 수정일시 |

**인덱스:**

```
- UNIQUE INDEX: (user_id, attendance_date) — 하루 1회 출석 보장
- INDEX: (attendance_date, daily_rank) — 일별 순위 조회
- INDEX: (attendance_date, created_at) — 일별 출석 시각순 조회
- INDEX: (user_id, attendance_date DESC) — 사용자별 최근 출석 조회
- INDEX: (user_id, consecutive_days) — 연속 출석 조회
```

### 3.2 `attendance_bonuses` 테이블 (보너스 기록)

연속출석 보너스 및 순위 보너스 지급 기록.

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | `bigint unsigned` PK | 자동 증가 ID |
| `user_id` | `bigint unsigned` FK | 회원 ID |
| `attendance_id` | `bigint unsigned` FK NULL | 출석 기록 ID |
| `bonus_type` | `varchar(20)` | 보너스 유형 (`rank_1`, `rank_2`, `rank_3`, `weekly`, `monthly`, `yearly`) |
| `bonus_point` | `int` | 보너스 포인트 |
| `bonus_date` | `date` | 보너스 적용 날짜 |
| `description` | `varchar(200)` | 보너스 설명 |
| `created_at` | `timestamp` | 생성일시 |

**인덱스:**

```
- INDEX: (user_id, bonus_date) — 사용자별 보너스 조회
- INDEX: (bonus_date, bonus_type) — 일별 보너스 현황
- UNIQUE INDEX: (user_id, bonus_date, bonus_type) — 중복 보너스 방지
```

---
