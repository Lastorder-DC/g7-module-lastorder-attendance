# 그누보드7 출석부 모듈 (lastorder-attendance)

[![PHPUnit](https://github.com/Lastorder-DC/g7-module-lastorder-attendance/actions/workflows/tests.yml/badge.svg)](https://github.com/Lastorder-DC/g7-module-lastorder-attendance/actions/workflows/tests.yml)

매일 출석 체크와 포인트 보상을 제공하는 그누보드7(Gnuboard7) 모듈입니다.

## 주요 기능

- ✅ **매일 출석 체크** — 하루 1회, 인삿말과 함께 출석
- 🏆 **순위 보너스** — 1~3위 출석자에게 추가 포인트
- 🔥 **연속출석 보너스** — 7일/30일/365일 연속출석 시 보너스 포인트
- 🎲 **랜덤 포인트** — 확률 기반 추가 포인트 지급
- 🤖 **자동출석** — 로그인 시 자동 출석 처리
- 📅 **월별 캘린더** — 출석/결석 현황을 캘린더로 확인
- ⚙️ **관리자 설정** — 포인트, 시간 제한, 보너스 등 모든 설정 관리
- 🌐 **다국어 지원** — 한국어, 영어

## 요구 사항

- **Gnuboard7** >= 7.0.0-beta.1
- **PHP** >= 8.2

## 설치

### 1. 모듈 파일 배치

그누보드7 설치 디렉토리의 `modules/` 폴더에 이 모듈을 배치합니다:

```
gnuboard7/
└── modules/
    └── lastorder-attendance/
        ├── module.json
        ├── module.php
        ├── composer.json
        ├── config/
        ├── database/
        ├── resources/
        └── src/
```

### 2. 그누보드7 관리자에서 모듈 설치

관리자 페이지에서 **모듈 관리** → **출석부** 모듈을 설치합니다.
설치 시 자동으로:
- 데이터베이스 테이블 생성 (`attendances`, `attendance_bonuses`)
- 기본 설정값 시딩
- 기본 인삿말 목록 시딩

## API 엔드포인트

모든 API는 `/api/modules/lastorder-attendance` 접두사를 사용합니다.

### 사용자 API (인증 필수: `auth:sanctum`)

| 메서드 | 경로 | 설명 |
|--------|------|------|
| `POST` | `/check-in` | 출석 체크 |
| `GET` | `/my` | 내 출석 현황 |
| `GET` | `/calendar/{year}/{month}` | 월별 출석 캘린더 |
| `GET` | `/status` | 출석 가능 상태 조회 |

### 사용자 API (인증 선택: `optional.sanctum`)

| 메서드 | 경로 | 설명 |
|--------|------|------|
| `GET` | `/today` | 오늘 출석 목록 |
| `GET` | `/greeting` | 랜덤 인삿말 조회 |

### 관리자 API (`auth:sanctum` + `admin`)

| 메서드 | 경로 | 설명 | 권한 |
|--------|------|------|------|
| `GET` | `/admin/attendance` | 출석 현황 목록 | `attendance.read` |
| `DELETE` | `/admin/attendance/{id}` | 출석 기록 삭제 | `attendance.delete` |
| `POST` | `/admin/attendance/recalculate-consecutive/{userId}` | 연속출석 재계산 | `attendance.read` |
| `POST` | `/admin/attendance/recalculate-total/{userId}` | 총출석 재계산 | `attendance.read` |
| `GET` | `/admin/settings` | 설정 조회 | `settings.read` |
| `PUT` | `/admin/settings` | 설정 수정 | `settings.update` |

## 설정 항목

| 항목 | 설명 | 기본값 |
|------|------|--------|
| `base_point` | 기본 출석 포인트 | `10` |
| `allowed_start_time` | 출석 시작 시간 (HH:MM) | `00:00` |
| `allowed_end_time` | 출석 종료 시간 (HH:MM) | `23:59` |
| `auto_attendance_enabled` | 자동출석 활성화 | `false` |
| `rank_1_bonus` ~ `rank_3_bonus` | 순위 보너스 포인트 | `50`, `30`, `20` |
| `weekly_bonus` | 7일 연속출석 보너스 | `100` |
| `monthly_bonus` | 30일 연속출석 보너스 | `500` |
| `yearly_bonus` | 365일 연속출석 보너스 | `5000` |
| `random_point_enabled` | 랜덤 포인트 활성화 | `false` |
| `random_point_min` / `max` | 랜덤 포인트 범위 | `1` ~ `100` |
| `random_point_chance` | 랜덤 포인트 당첨 확률 (%) | `30` |

## 디렉토리 구조

```
lastorder-attendance/
├── config/                         # 설정 파일
│   └── attendance.php
├── database/
│   ├── factories/                  # 테스트용 팩토리
│   ├── migrations/                 # DB 마이그레이션
│   └── seeders/                    # 기본 데이터 시더
├── resources/
│   ├── lang/                       # 다국어 (JSON)
│   ├── layouts/                    # JSON 레이아웃 (admin, user)
│   └── routes/                     # 라우트 설정 (JSON)
├── src/
│   ├── Enums/                      # BonusType enum
│   ├── Http/
│   │   ├── Controllers/Api/        # API 컨트롤러
│   │   ├── Requests/               # FormRequest 검증
│   │   └── Resources/              # API 리소스
│   ├── Listeners/                  # Hook 리스너
│   ├── Models/                     # Eloquent 모델
│   ├── Providers/                  # Service Provider
│   ├── Repositories/               # Repository 패턴
│   ├── Services/                   # 비즈니스 로직
│   ├── lang/                       # 다국어 (PHP)
│   └── routes/                     # API 라우트
├── tests/
│   ├── Feature/                    # 기능 테스트
│   └── Unit/                       # 단위 테스트
├── module.json                     # 모듈 메타데이터
├── module.php                      # Module 클래스
├── composer.json
├── CHANGELOG.md
└── LICENSE
```

## 테스트

```bash
# 의존성 설치
composer install

# 전체 테스트 실행
vendor/bin/phpunit

# Feature 테스트만 실행
vendor/bin/phpunit --testsuite Feature

# Unit 테스트만 실행
vendor/bin/phpunit --testsuite Unit
```

## 라이선스

MIT License — 자세한 내용은 [LICENSE](LICENSE) 파일을 참고하세요.