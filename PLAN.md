# 그누보드7 출석부 모듈 개발 계획서

> **모듈 식별자**: `lastorder-attendance`
> **네임스페이스**: `Modules\Lastorder\Attendance\`
> **Composer 패키지명**: `modules/lastorder-attendance`
> **대상 플랫폼**: Gnuboard7 (Laravel 12 + React 19)

---

## 분할 문서 목차

1. [모듈 개요](./PLAN-1.md)
2. [디렉토리 구조](./PLAN-2.md)
3. [데이터베이스 설계](./PLAN-3.md)
4. [백엔드 아키텍처](./PLAN-4.md)
5. [API 설계](./PLAN-5.md)
6. [프론트엔드 (JSON 레이아웃)](./PLAN-6.md)
7. [설정 시스템](./PLAN-7.md)
8. [포인트 및 보너스 시스템](./PLAN-8.md)
9. [자동출석 시스템](./PLAN-9.md)
10. [다국어 지원](./PLAN-10.md)
11. [권한 및 메뉴](./PLAN-11.md)
12. [구현 단계별 계획](./PLAN-12.md)

---

## 부록: 참고 자료

### A. 참고한 기존 모듈

| 모듈 | 참고 포인트 |
|------|-------------|
| `sirsoft-board` | Module 클래스 구조, 권한/메뉴 정의, Service-Repository 패턴, JSON 레이아웃, 라우트 정의 |
| `sirsoft-ecommerce` | module.json assets 설정, Hook 리스너, ServiceProvider 패턴 |
| `sirsoft-page` | 간결한 모듈 구조 참고, 간단한 CRUD 패턴 |

### B. 기술 스택

- **백엔드**: PHP 8.2+, Laravel 12.x
- **프론트엔드**: React 19 (JSON 레이아웃 엔진을 통한 선언적 UI)
- **데이터베이스**: MySQL 8.0+ (utf8mb4)
- **테스트**: PHPUnit 11.x, Vitest
- **코드 스타일**: Laravel Pint (PSR-12)
