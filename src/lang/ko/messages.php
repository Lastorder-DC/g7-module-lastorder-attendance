<?php

return [
    // 출석 관련
    'check_in_success' => '출석 완료! 오늘도 좋은 하루 되세요.',
    'already_checked_in' => '오늘은 이미 출석하셨습니다.',
    'not_attendance_time' => '현재 출석 가능 시간이 아닙니다.',
    'default_greeting' => '안녕하세요!',

    // 출석 현황
    'status_fetched' => '출석 현황을 조회했습니다.',
    'today_list_fetched' => '오늘 출석 목록을 조회했습니다.',
    'calendar_fetched' => '캘린더 데이터를 조회했습니다.',
    'attendance_list_fetched' => '출석 목록을 조회했습니다.',
    'greeting_fetched' => '인삿말을 조회했습니다.',

    // 관리자
    'admin' => [
        'attendance_deleted' => '출석 기록이 삭제되었습니다.',
        'attendance_not_found' => '출석 기록을 찾을 수 없습니다.',
        'consecutive_recalculated' => '연속출석 일수가 재계산되었습니다.',
        'settings_fetched' => '설정을 조회했습니다.',
        'settings_saved' => '설정이 저장되었습니다.',
        'settings_save_failed' => '설정 저장에 실패했습니다.',
        'delete_failed' => '출석 기록 삭제에 실패했습니다.',
    ],

    // 연속출석
    'consecutive' => [
        'weekly' => '주간 (7일)',
        'monthly' => '월간 (30일)',
        'yearly' => '연간 (365일)',
    ],

    // 검증
    'validation' => [
        'greeting_max' => '인삿말은 200자 이내로 입력해주세요.',
        'base_point_min' => '기본 포인트는 0 이상이어야 합니다.',
        'start_hour_min' => '시작 시간은 0 이상이어야 합니다.',
        'start_hour_max' => '시작 시간은 23 이하이어야 합니다.',
        'end_hour_min' => '종료 시간은 1 이상이어야 합니다.',
        'end_hour_max' => '종료 시간은 24 이하이어야 합니다.',
    ],

    // 자동출석
    'auto_attendance' => [
        'success' => '자동출석이 완료되었습니다.',
        'disabled' => '자동출석이 비활성화되어 있습니다.',
    ],

    // 활동 로그
    'activity' => [
        'check_in' => '출석 체크',
        'auto_check_in' => '자동 출석',
        'admin_delete' => '관리자 출석 삭제',
    ],
];
