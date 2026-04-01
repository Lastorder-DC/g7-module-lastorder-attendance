<?php

namespace Modules\Lastorder\Attendance\Providers;

use App\Extension\BaseModuleServiceProvider;
use Modules\Lastorder\Attendance\Repositories\AttendanceBonusRepository;
use Modules\Lastorder\Attendance\Repositories\AttendanceRepository;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceBonusRepositoryInterface;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceRepositoryInterface;

/**
 * 출석부 모듈 서비스 프로바이더
 *
 * Repository 인터페이스와 구현체 바인딩을 담당합니다.
 */
class AttendanceServiceProvider extends BaseModuleServiceProvider
{
    /**
     * 모듈 식별자
     */
    protected string $moduleIdentifier = 'lastorder-attendance';

    /**
     * Repository 인터페이스와 구현체 매핑
     *
     * @var array<class-string, class-string>
     */
    protected array $repositories = [
        AttendanceRepositoryInterface::class => AttendanceRepository::class,
        AttendanceBonusRepositoryInterface::class => AttendanceBonusRepository::class,
    ];
}
