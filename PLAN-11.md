## 11. 권한 및 메뉴

### 11.1 권한 구조

```php
// module.php → getPermissions()
public function getPermissions(): array
{
    return [
        'name' => [
            'ko' => '출석부',
            'en' => 'Attendance',
        ],
        'description' => [
            'ko' => '출석부 모듈 권한',
            'en' => 'Attendance module permissions',
        ],
        'categories' => [
            // 출석 관리 권한
            [
                'identifier' => 'attendance',
                'name' => [
                    'ko' => '출석 관리',
                    'en' => 'Attendance Management',
                ],
                'description' => [
                    'ko' => '출석 기록 관리 권한',
                    'en' => 'Attendance record management permissions',
                ],
                'permissions' => [
                    [
                        'action' => 'read',
                        'name' => ['ko' => '출석 현황 조회', 'en' => 'View Attendance'],
                        'description' => ['ko' => '출석 현황 목록 조회', 'en' => 'View attendance list'],
                        'type' => 'admin',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'action' => 'delete',
                        'name' => ['ko' => '출석 기록 삭제', 'en' => 'Delete Attendance'],
                        'description' => ['ko' => '출석 기록 삭제', 'en' => 'Delete attendance record'],
                        'type' => 'admin',
                        'roles' => ['admin'],
                    ],
                ],
            ],
            // 환경설정 권한
            [
                'identifier' => 'settings',
                'name' => [
                    'ko' => '환경설정',
                    'en' => 'Settings',
                ],
                'description' => [
                    'ko' => '출석부 환경설정 권한',
                    'en' => 'Attendance settings permissions',
                ],
                'permissions' => [
                    [
                        'action' => 'read',
                        'name' => ['ko' => '환경설정 조회', 'en' => 'View Settings'],
                        'description' => ['ko' => '출석부 환경설정 조회', 'en' => 'View attendance settings'],
                        'type' => 'admin',
                        'roles' => ['admin'],
                    ],
                    [
                        'action' => 'update',
                        'name' => ['ko' => '환경설정 수정', 'en' => 'Update Settings'],
                        'description' => ['ko' => '출석부 환경설정 수정', 'en' => 'Update attendance settings'],
                        'type' => 'admin',
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ],
    ];
}
```

### 11.2 관리자 메뉴

```php
// module.php → getAdminMenus()
public function getAdminMenus(): array
{
    return [
        [
            'name' => [
                'ko' => '출석부 관리',
                'en' => 'Attendance Management',
            ],
            'slug' => 'lastorder-attendance',
            'url' => null,
            'icon' => 'fas fa-calendar-check',
            'order' => 35,
            'children' => [
                [
                    'name' => [
                        'ko' => '환경설정',
                        'en' => 'Settings',
                    ],
                    'slug' => 'lastorder-attendance-settings',
                    'url' => '/admin/attendance/settings',
                    'icon' => 'fas fa-cog',
                    'order' => 1,
                    'permission' => 'lastorder-attendance.settings.read',
                ],
                [
                    'name' => [
                        'ko' => '출석 현황',
                        'en' => 'Attendance List',
                    ],
                    'slug' => 'lastorder-attendance-list',
                    'url' => '/admin/attendance',
                    'icon' => 'fas fa-list',
                    'order' => 2,
                    'permission' => 'lastorder-attendance.attendance.read',
                ],
            ],
        ],
    ];
}
```

---
