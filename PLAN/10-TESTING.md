# 10. 테스트 전략

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 09-I18N](09-I18N.md)

---

## 1. 테스트 원칙 (AGENTS.md)

```
기능 구현 = 테스트 코드 작성 필수
테스트 통과 = 작업 완료 (작성만으로 불충분!)
기존 테스트 있음 → 변경사항 반영하여 수정 후 실행
기능 구현 시 관련된 모든 계층(백엔드+프론트엔드+레이아웃 렌더링) 테스트 필수
```

---

## 2. 테스트 환경

### 2.1 백엔드 테스트 (PHPUnit)

```bash
# 전체 실행
composer install && vendor/bin/phpunit

# PHP 구문 검사
find src database -name "*.php" -exec php -l {} \;
```

- PHP 8.2 / 8.3 / 8.4 대응
- 모듈은 독립 실행 (전체 Laravel 앱 없이)
- `tests/stubs.php`에서 필요한 코어 클래스 스텁 제공
- `tests/bootstrap.php`에서 autoload + stubs 로드

### 2.2 프론트엔드 테스트 (Vitest)

```bash
# 레이아웃 렌더링 테스트
npx vitest run
```

- `vitest.config.ts` 독립 설정 (루트 config 포함 금지)
- `createLayoutTest()` 유틸리티 사용
- `mockApi()`로 API 응답 모킹

---

## 3. 백엔드 테스트 목록

### 3.1 Feature 테스트

#### CheckInTest.php

| 테스트 | 설명 |
|--------|------|
| `test_user_can_check_in` | 인증 사용자 출석 체크 성공 |
| `test_guest_cannot_check_in` | 비인증 사용자 출석 불가 |
| `test_user_cannot_check_in_twice` | 중복 출석 방지 |
| `test_check_in_with_greeting` | 인삿말 포함 출석 |
| `test_check_in_without_greeting_uses_random` | 빈 인삿말 시 랜덤 기본값 사용 |
| `test_check_in_greeting_max_length` | 인삿말 200자 제한 검증 |
| `test_check_in_assigns_daily_rank` | 순위 자동 부여 |
| `test_check_in_calculates_consecutive_days` | 연속출석 일수 계산 |
| `test_check_in_calculates_total_days` | 총 출석일수 계산 |
| `test_check_in_outside_time_restriction` | 시간 제한 외 출석 불가 |
| `test_check_in_within_time_restriction` | 시간 제한 내 출석 가능 |
| `test_check_in_no_time_restriction` | 시간 제한 없음 = 항상 가능 |

#### BonusTest.php

| 테스트 | 설명 |
|--------|------|
| `test_rank_1st_bonus` | 1등 보너스 포인트 지급 |
| `test_rank_2nd_bonus` | 2등 보너스 포인트 지급 |
| `test_rank_3rd_bonus` | 3등 보너스 포인트 지급 |
| `test_rank_4th_no_bonus` | 4등 이하 보너스 없음 |
| `test_consecutive_7_days_bonus` | 7일 연속 보너스 |
| `test_consecutive_30_days_bonus` | 30일 연속 보너스 |
| `test_consecutive_365_days_bonus` | 365일 연속 보너스 |
| `test_consecutive_non_multiple_no_bonus` | 배수가 아니면 보너스 없음 |
| `test_random_point_within_range` | 랜덤 포인트 범위 내 |
| `test_random_point_disabled` | 랜덤 포인트 비활성 시 0 |
| `test_total_point_calculation` | 총 포인트 = 기본 + 보너스 합계 |

#### AutoAttendanceTest.php

| 테스트 | 설명 |
|--------|------|
| `test_auto_attendance_on_login` | 로그인 시 자동출석 |
| `test_auto_attendance_disabled` | 설정 비활성 시 자동출석 안 함 |
| `test_auto_attendance_already_checked_in` | 이미 출석 시 자동출석 skip |
| `test_auto_attendance_failure_does_not_block_login` | 자동출석 실패해도 로그인 정상 |
| `test_auto_attendance_marks_is_auto_true` | 자동출석은 is_auto=true |

#### Admin/AttendanceAdminTest.php

| 테스트 | 설명 |
|--------|------|
| `test_admin_can_view_attendance_list` | 관리자 출석 현황 조회 |
| `test_admin_can_filter_by_date` | 날짜별 필터 |
| `test_admin_can_delete_attendance` | 출석 기록 삭제 |
| `test_admin_can_recalculate_consecutive` | 연속출석 재계산 |
| `test_non_admin_cannot_access` | 일반 사용자 접근 불가 (403) |

#### Admin/SettingsTest.php

| 테스트 | 설명 |
|--------|------|
| `test_admin_can_view_settings` | 설정 조회 |
| `test_admin_can_update_settings` | 설정 저장 |
| `test_settings_validation` | 설정값 검증 (음수 포인트 등) |

### 3.2 Unit 테스트

#### AttendanceServiceTest.php

| 테스트 | 설명 |
|--------|------|
| `test_can_check_in_returns_true_for_new_user` | 미출석 사용자 → true |
| `test_can_check_in_returns_false_if_already_checked` | 이미 출석 → false |
| `test_can_check_in_respects_time_restriction` | 시간 제한 반영 |

#### AttendanceBonusServiceTest.php

| 테스트 | 설명 |
|--------|------|
| `test_calculate_rank_bonus` | 순위 보너스 계산 |
| `test_calculate_consecutive_bonus` | 연속출석 보너스 계산 |
| `test_calculate_random_bonus` | 랜덤 보너스 계산 |
| `test_calculate_all_bonuses` | 전체 보너스 합산 |

---

## 4. 테스트 스텁 (tests/stubs.php)

모듈이 독립적으로 테스트를 실행하기 위해 코어 클래스의 스텁 필요:

```php
<?php

// FormRequest 스텁
if (!class_exists(\Illuminate\Foundation\Http\FormRequest::class)) {
    // ... 스텁 정의
}

// BaseApiController 스텁
if (!class_exists(\App\Http\Controllers\Api\Base\BaseApiController::class)) {
    // ... 스텁 정의
}

// AdminBaseController 스텁
if (!class_exists(\App\Http\Controllers\Api\Base\AdminBaseController::class)) {
    // ... 스텁 정의
}

// AuthBaseController 스텁
if (!class_exists(\App\Http\Controllers\Api\Base\AuthBaseController::class)) {
    // ... 스텁 정의
}

// HookListenerInterface 스텁
if (!interface_exists(\App\Contracts\Extension\HookListenerInterface::class)) {
    // ... 스텁 정의
}

// ModuleSettingsInterface 스텁
if (!interface_exists(\App\Contracts\Extension\ModuleSettingsInterface::class)) {
    // ... 스텁 정의
}

// __() 함수 스텁 (i18n)
if (!function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null): string {
        return $key;
    }
}

// module_setting() 스텁
if (!function_exists('module_setting')) {
    function module_setting(string $module, ?string $key = null, mixed $default = null): mixed {
        return $default;
    }
}
```

---

## 5. 레이아웃 렌더링 테스트

### 테스트 파일 위치

```
resources/js/__tests__/layouts/
├── user_attendance.test.tsx
├── admin_attendance_index.test.tsx
└── admin_attendance_settings.test.tsx
```

### 테스트 예시

```typescript
import { createLayoutTest, screen } from '../utils/layoutTestUtils';
import userAttendanceLayout from '../../layouts/user/user_attendance.json';

describe('user_attendance 레이아웃', () => {
  const testUtils = createLayoutTest(userAttendanceLayout);

  beforeEach(() => {
    testUtils.mockApi('todayList', {
      response: {
        success: true,
        data: {
          data: [
            { id: 1, daily_rank: 1, attended_time: '00:00:01', greeting: '안녕', user_nick: '테스트', base_point: 10, random_point: 0, consecutive_days: 1, total_days: 1 }
          ],
          pagination: { total: 1, from: 1, to: 1, per_page: 20, current_page: 1, last_page: 1 }
        }
      }
    });

    testUtils.mockApi('attendanceStatus', {
      response: {
        success: true,
        data: { can_check_in: true, already_checked_in: false }
      }
    });
  });

  afterEach(() => {
    testUtils.cleanup();
  });

  it('출석 목록이 렌더링된다', async () => {
    await testUtils.render();
    expect(screen.getByText('테스트')).toBeInTheDocument();
  });

  it('로그인하지 않으면 출석 폼이 숨겨진다', async () => {
    // _global.currentUser가 없는 경우
    await testUtils.render();
    expect(screen.queryByText('출석하기')).not.toBeInTheDocument();
  });
});
```

---

## 6. CI 워크플로우

### .github/workflows/tests.yml

```yaml
name: Tests

on: [push, pull_request]

permissions:
  contents: read

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: ['8.2', '8.3', '8.4']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
      - run: composer install --no-progress
      - run: vendor/bin/phpunit
      - run: find src database -name "*.php" -exec php -l {} \;
```

---

## 다음: [11-PROHIBITED-PATTERNS.md](11-PROHIBITED-PATTERNS.md) →
