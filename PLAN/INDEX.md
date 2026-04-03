# 그누보드7 출석부 모듈 재설계 계획

> **모듈 식별자**: `lastorder-attendance`  
> **네임스페이스**: `Modules\Lastorder\Attendance\`  
> **시작 버전**: `1.0.9` (기존 1.0.8까지 사용됨)  
> **대상 g7 버전**: `>=7.0.0-beta.1`  
> **라이선스**: MIT

---

## 📋 문서 목록

| # | 문서 | 설명 |
|---|------|------|
| 1 | [01-OVERVIEW.md](01-OVERVIEW.md) | 프로젝트 개요 및 디렉토리 구조 |
| 2 | [02-DATABASE.md](02-DATABASE.md) | 데이터베이스 설계 (마이그레이션, 모델) |
| 3 | [03-BACKEND-ARCHITECTURE.md](03-BACKEND-ARCHITECTURE.md) | 백엔드 아키텍처 (Service-Repository 패턴) |
| 4 | [04-API-DESIGN.md](04-API-DESIGN.md) | API 엔드포인트 설계 |
| 5 | [05-FRONTEND-USER.md](05-FRONTEND-USER.md) | 사용자 출석 페이지 레이아웃 |
| 6 | [06-FRONTEND-ADMIN.md](06-FRONTEND-ADMIN.md) | 관리자 페이지 레이아웃 |
| 7 | [07-SETTINGS-SYSTEM.md](07-SETTINGS-SYSTEM.md) | 모듈 설정 시스템 |
| 8 | [08-HOOK-SYSTEM.md](08-HOOK-SYSTEM.md) | 훅 시스템 및 자동출석 |
| 9 | [09-I18N.md](09-I18N.md) | 다국어 지원 (ko/en) |
| 10 | [10-TESTING.md](10-TESTING.md) | 테스트 전략 |
| 11 | [11-PROHIBITED-PATTERNS.md](11-PROHIBITED-PATTERNS.md) | AGENTS.md 금지 패턴 정리 |
| 12 | [12-IMPLEMENTATION-ORDER.md](12-IMPLEMENTATION-ORDER.md) | 구현 순서 및 체크리스트 |

---

## 🎯 핵심 요구사항 요약

### 출석 기능
- 매일 1회 출석 체크 (인삿말 입력, 랜덤 기본값)
- 연속출석 보너스 (주 7일 / 월 30일 / 연 365일)
- 매일 1위~3위 보너스
- 출석/결석/미출석 캘린더 표시
- 현재 시간 실시간 표시
- 자세히 보기 드롭다운

### 설정
- 자동출석 사용 여부 (로그인 시 자동 출석)
- 출석 가능 시간 범위 설정
- 랜덤 포인트 사용 여부 및 범위

### 주의사항
- 버전 1.0.9 이상부터 시작
- AGENTS.md 규칙 엄격 준수
- 유저 페이지에서 관리자 전용 API 사용 금지

---

## 🔗 참조 문서

- [gnuboard/g7 AGENTS.md](https://github.com/gnuboard/g7/blob/main/AGENTS.md)
- [모듈 개발 기초](https://github.com/gnuboard/g7/blob/main/docs/extension/module-basics.md)
- [모듈 라우트 규칙](https://github.com/gnuboard/g7/blob/main/docs/extension/module-routing.md)
- [모듈 레이아웃 시스템](https://github.com/gnuboard/g7/blob/main/docs/extension/module-layouts.md)
- [모듈 설정 시스템](https://github.com/gnuboard/g7/blob/main/docs/extension/module-settings.md)
- [모듈 다국어](https://github.com/gnuboard/g7/blob/main/docs/extension/module-i18n.md)
- [훅 시스템](https://github.com/gnuboard/g7/blob/main/docs/extension/hooks.md)
- [레이아웃 확장](https://github.com/gnuboard/g7/blob/main/docs/extension/layout-extensions.md)
- [Service-Repository 패턴](https://github.com/gnuboard/g7/blob/main/docs/backend/service-repository.md)
- [컨트롤러 계층](https://github.com/gnuboard/g7/blob/main/docs/backend/controllers.md)
- [API 응답 규칙](https://github.com/gnuboard/g7/blob/main/docs/backend/response-helper.md)
