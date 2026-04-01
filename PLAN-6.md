## 6. 프론트엔드 (JSON 레이아웃)

그누보드7의 JSON 레이아웃 엔진을 활용하여 프론트엔드 빌드 없이 UI를 구성한다.

### 6.1 사용자 출석부 페이지 (`user_attendance.json`)

참고 이미지의 UI를 기반으로 다음 섹션들을 구성한다:

#### 상단 정보 바

- 현재 날짜 및 시간 표시
- 관리자용: 설정, 스킨관리, 관리 버튼
- 이전달/이번달/다음달 네비게이션

#### 출석 현황 요약

| 항목 | 설명 |
|------|------|
| 출석점수 | 오늘 획득한 총 포인트 |
| 출석시간 | 출석 시각 or "하루 종일" |
| 개근점수 | 연속출석 보너스 포인트 |
| 진행상태 | 출석가능/출석마감 |
| 랭킹점수 | 순위 보너스 포인트 |
| 출석여부 | 출석완료/미출석 |
| 출석권한 | 로그인 사용자/비회원 |
| 개근분류 | 현재 연속출석 일수 |

#### 출석 캘린더 (월별)

- 1~30(31)일 숫자 표시
- **출석한 날**: 파란색 강조 (●)
- **결석한 날**: 빨간색 강조 (●)
- **미출석 (미래/가입전)**: 회색
- 하단 범례: 결석 ● 출석 ● 미출석

#### 출석 체크 폼

- "출석이 완료되었습니다" / "출석하기" 메시지 영역
- 인삿말 입력 필드 (기본값: 랜덤 인삿말)
- 출석 버튼

#### 오늘 출석 목록 테이블

| 컬럼 | 설명 |
|------|------|
| 순위 | 당일 출석 순위 |
| 출석시각 | HH:MM:SS |
| 인삿말 | 입력한 인삿말 (마스킹 처리 옵션) |
| 별명 | 회원 닉네임/이름 |
| 포인트 | 기본 포인트 |
| 랜덤 포인트 | 랜덤 추가 포인트 (꽝/숫자) |
| 개근 | N일째 연속 출석 |
| 총 출석일 | 누적 출석 일수 |

- 페이지네이션 지원

### 6.2 관리자 설정 페이지 (`admin_attendance_settings.json`)

관리자 페이지에서 출석부 모듈의 모든 설정을 관리하는 화면.

#### 기본 설정 섹션

- 출석 기본 포인트 (숫자 입력)
- 출석 가능 시작 시간 (시간 선택)
- 출석 가능 종료 시간 (시간 선택)

#### 자동출석 설정 섹션

- 자동출석 사용여부 (토글)
- 자동출석 시 인삿말 (기본 인삿말 사용/빈값)

#### 순위 보너스 설정 섹션

- 1위 보너스 포인트
- 2위 보너스 포인트
- 3위 보너스 포인트

#### 연속출석 보너스 설정 섹션

- 주간(7일) 연속출석 보너스 포인트
- 월간(30일) 연속출석 보너스 포인트
- 연간(365일) 연속출석 보너스 포인트

#### 랜덤 포인트 설정 섹션

- 랜덤 포인트 사용여부 (토글)
- 최소 랜덤 포인트
- 최대 랜덤 포인트
- 랜덤 포인트 당첨 확률 (%)

#### 인삿말 설정 섹션

- 기본 인삿말 목록 (추가/삭제 가능)

### 6.3 관리자 출석 현황 페이지 (`admin_attendance_index.json`)

- 날짜별 출석 현황 조회
- 회원 검색 필터
- 출석 기록 삭제 기능
- 통계 요약 (일별 출석자 수, 보너스 지급 현황)

### 6.4 프론트엔드 라우트

#### `resources/routes/admin.json`

```json
{
    "version": "1.0.0",
    "routes": [
        {
            "path": "*/admin/attendance/settings",
            "layout": "admin_attendance_settings",
            "auth_required": true,
            "meta": {
                "title": "$t:lastorder-attendance.admin.settings.title",
                "permission": "lastorder-attendance.settings.read"
            }
        },
        {
            "path": "*/admin/attendance",
            "layout": "admin_attendance_index",
            "auth_required": true,
            "meta": {
                "title": "$t:lastorder-attendance.admin.attendance.title",
                "permission": "lastorder-attendance.attendance.read"
            }
        }
    ]
}
```

#### `resources/routes/user.json`

```json
{
    "version": "1.0.0",
    "routes": [
        {
            "path": "*/attendance",
            "layout": "user_attendance",
            "auth_required": false,
            "meta": {
                "title": "$t:lastorder-attendance.user.title"
            }
        }
    ]
}
```

---
