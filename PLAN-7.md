## 7. 설정 시스템

그누보드7의 설정 시스템을 활용하여 관리자가 변경 가능한 설정을 DB에 저장한다.

### 7.1 설정 항목

```php
// config/attendance.php
return [
    // 기본 출석 포인트
    'base_point' => 10,

    // 출석 가능 시간 (24시간 형식)
    'allowed_start_time' => '01:00',    // 오전 1시
    'allowed_end_time' => '23:00',      // 오후 11시

    // 자동출석 설정
    'auto_attendance_enabled' => false,
    'auto_attendance_greeting' => '',   // 빈 문자열이면 랜덤 인삿말 사용

    // 순위 보너스 포인트
    'rank_1_bonus' => 50,
    'rank_2_bonus' => 30,
    'rank_3_bonus' => 20,

    // 연속출석 보너스 포인트
    'weekly_bonus' => 100,           // 7일 연속
    'monthly_bonus' => 500,          // 30일 연속
    'yearly_bonus' => 5000,          // 365일 연속

    // 랜덤 포인트 설정
    'random_point_enabled' => false,
    'random_point_min' => 1,
    'random_point_max' => 100,
    'random_point_chance' => 30,     // 당첨 확률 (%)

    // 기본 인삿말 목록
    'default_greetings' => [
        '좋은 아침이에요~',
        '오늘도 화이팅!',
        '반갑습니다~',
        '좋은 하루 보내세요!',
        '안녕하세요!',
        '오늘도 좋은 하루~',
        '즐거운 하루 되세요!',
        '행복한 하루~',
        '꾸준함이 힘이에요!',
        '오늘도 출석 완료!',
    ],

    // 페이지네이션
    'per_page' => 20,

    // 캐시
    'cache' => [
        'enabled' => true,
        'ttl' => 60,   // 초 단위
    ],
];
```

### 7.2 설정 저장 방식

그누보드7의 코어 설정 시스템(`settings` 테이블)을 통해 DB에 저장한다.
`config/settings/attendance.php`에 DB 저장 설정의 기본값과 유효성 검증 규칙을 정의한다.

---
