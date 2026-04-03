<?php

namespace Modules\Lastorder\Attendance;

use App\Extension\AbstractModule;

class Module extends AbstractModule
{
    // getName(), getVersion(), getDescription()은 module.json에서 자동 파싱
    // getIdentifier(), getVendor()는 디렉토리명에서 자동 추론

    /**
     * 관리자 메뉴 정의
     */
    public function getAdminMenus(): array
    {
        return [
            [
                'name' => [
                    'ko' => '출석부',
                    'en' => 'Attendance',
                ],
                'slug' => 'lastorder-attendance',
                'url' => '/admin/attendance',
                'icon' => 'fa-calendar-check',
                'order' => 50,
                'children' => [
                    [
                        'name' => [
                            'ko' => '출석 현황',
                            'en' => 'Attendance Status',
                        ],
                        'slug' => 'lastorder-attendance-index',
                        'url' => '/admin/attendance',
                        'icon' => 'fa-list',
                        'order' => 1,
                        'permission' => 'lastorder-attendance.admin.view',
                    ],
                    [
                        'name' => [
                            'ko' => '출석부 설정',
                            'en' => 'Attendance Settings',
                        ],
                        'slug' => 'lastorder-attendance-settings',
                        'url' => '/admin/attendance/settings',
                        'icon' => 'fa-cog',
                        'order' => 2,
                        'permission' => 'lastorder-attendance.admin.settings',
                    ],
                ],
            ],
        ];
    }

    /**
     * 권한 목록
     */
    public function getPermissions(): array
    {
        return [
            [
                'identifier' => 'lastorder-attendance.admin.view',
                'name' => ['ko' => '출석 현황 조회', 'en' => 'View Attendance'],
                'description' => ['ko' => '출석 현황을 조회할 수 있습니다', 'en' => 'Can view attendance status'],
                'roles' => ['admin'],
            ],
            [
                'identifier' => 'lastorder-attendance.admin.manage',
                'name' => ['ko' => '출석 기록 관리', 'en' => 'Manage Attendance'],
                'description' => ['ko' => '출석 기록을 삭제/재계산할 수 있습니다', 'en' => 'Can delete/recalculate attendance'],
                'roles' => ['admin'],
            ],
            [
                'identifier' => 'lastorder-attendance.admin.settings',
                'name' => ['ko' => '출석부 설정', 'en' => 'Attendance Settings'],
                'description' => ['ko' => '출석부 설정을 변경할 수 있습니다', 'en' => 'Can change attendance settings'],
                'roles' => ['admin'],
            ],
        ];
    }

    /**
     * 훅 리스너 목록
     */
    public function getHookListeners(): array
    {
        return [
            \Modules\Lastorder\Attendance\Listeners\AutoAttendanceListener::class,
            \Modules\Lastorder\Attendance\Listeners\AttendanceActivityLogListener::class,
        ];
    }
}
