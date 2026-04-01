## 9. 자동출석 시스템

### 9.1 동작 방식

그누보드7의 Hook 시스템을 활용하여, 로그인 성공 이벤트에 자동출석 리스너를 등록한다.

```
AutoAttendanceListener:
1. 자동출석 설정 확인 (auto_attendance_enabled)
2. 비활성화 시: 아무것도 하지 않음
3. 활성화 시:
   a. 출석 가능 시간 확인
   b. 이미 출석했는지 확인
   c. 미출석 시: 자동 출석 처리
      - 인삿말: 설정된 값 또는 랜덤 인삿말
      - is_auto: true로 기록
```

### 9.2 Hook 리스너 등록

```php
// module.php
public function getHookListeners(): array
{
    return [
        AutoAttendanceListener::class,
        AttendanceActivityLogListener::class,
    ];
}
```

`AutoAttendanceListener`는 코어의 로그인 성공 훅(`auth.login.success` 등)을 구독하여
자동출석을 수행한다.

---
