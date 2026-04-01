<?php

namespace Modules\Lastorder\Attendance\Tests\Feature;

use Mockery;
use Mockery\MockInterface;
use Modules\Lastorder\Attendance\Listeners\AutoAttendanceListener;
use Modules\Lastorder\Attendance\Models\Attendance;
use Modules\Lastorder\Attendance\Services\AttendanceGreetingService;
use Modules\Lastorder\Attendance\Services\AttendanceService;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;
use PHPUnit\Framework\TestCase;

class AutoAttendanceTest extends TestCase
{
    private MockInterface $attendanceService;

    private MockInterface $settingsService;

    private MockInterface $greetingService;

    private AutoAttendanceListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attendanceService = Mockery::mock(AttendanceService::class);
        $this->settingsService = Mockery::mock(AttendanceSettingsService::class);
        $this->greetingService = Mockery::mock(AttendanceGreetingService::class);

        $this->listener = new AutoAttendanceListener(
            $this->attendanceService,
            $this->settingsService,
            $this->greetingService,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeUser(int $id = 1): object
    {
        return (object) ['id' => $id];
    }

    public function test_auto_attendance_on_login_when_enabled(): void
    {
        $user = $this->makeUser(1);
        $greeting = '자동 출석 인사';

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(true);

        $this->attendanceService->shouldReceive('canCheckIn')
            ->with(1)
            ->once()
            ->andReturn(true);

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_greeting', '')
            ->andReturn($greeting);

        $attendance = Mockery::mock(Attendance::class)->shouldIgnoreMissing();
        $attendance->shouldReceive('update')
            ->with(['is_auto' => true])
            ->once();

        $this->attendanceService->shouldReceive('checkIn')
            ->once()
            ->with(1, $greeting, Mockery::any())
            ->andReturn($attendance);

        $this->listener->onLogin($user);

        // Verify expectations were met
        $this->assertTrue(true);
    }

    public function test_auto_attendance_uses_random_greeting_when_setting_empty(): void
    {
        $user = $this->makeUser(1);
        $randomGreeting = '오늘도 화이팅!';

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(true);

        $this->attendanceService->shouldReceive('canCheckIn')
            ->with(1)
            ->once()
            ->andReturn(true);

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_greeting', '')
            ->andReturn('');

        $this->greetingService->shouldReceive('getRandomGreeting')
            ->once()
            ->andReturn($randomGreeting);

        $attendance = Mockery::mock(Attendance::class)->shouldIgnoreMissing();
        $attendance->shouldReceive('update')
            ->with(['is_auto' => true])
            ->once();

        $this->attendanceService->shouldReceive('checkIn')
            ->once()
            ->with(1, $randomGreeting, Mockery::any())
            ->andReturn($attendance);

        $this->listener->onLogin($user);

        $this->assertTrue(true);
    }

    public function test_auto_attendance_disabled_does_not_check_in(): void
    {
        $user = $this->makeUser(1);

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(false);

        $this->attendanceService->shouldNotReceive('checkIn');

        $this->listener->onLogin($user);

        $this->assertTrue(true);
    }

    public function test_auto_attendance_skipped_when_already_checked_in(): void
    {
        $user = $this->makeUser(1);

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(true);

        $this->attendanceService->shouldReceive('canCheckIn')
            ->with(1)
            ->andReturn(false);

        $this->attendanceService->shouldNotReceive('checkIn');

        $this->listener->onLogin($user);

        $this->assertTrue(true);
    }

    public function test_auto_attendance_skipped_when_user_is_null(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(true);

        $this->attendanceService->shouldNotReceive('checkIn');

        $this->listener->onLogin(null);

        $this->assertTrue(true);
    }

    public function test_auto_attendance_skipped_when_user_has_no_id(): void
    {
        $user = (object) ['name' => 'Test'];

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(true);

        $this->attendanceService->shouldNotReceive('checkIn');

        $this->listener->onLogin($user);

        $this->assertTrue(true);
    }

    public function test_get_subscribed_hooks_returns_correct_hook(): void
    {
        $hooks = AutoAttendanceListener::getSubscribedHooks();

        $this->assertIsArray($hooks);
        $this->assertArrayHasKey('core.auth.login.after', $hooks);

        $hookConfig = $hooks['core.auth.login.after'];
        $this->assertEquals('onLogin', $hookConfig['method']);
        $this->assertArrayHasKey('priority', $hookConfig);
        $this->assertEquals(50, $hookConfig['priority']);
    }

    public function test_handle_delegates_to_on_login(): void
    {
        $user = $this->makeUser(1);

        // When handle is called, it should delegate to onLogin
        // Since auto_attendance is disabled, checkIn should not be called
        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(false);

        $this->attendanceService->shouldNotReceive('checkIn');

        $this->listener->handle($user);

        $this->assertTrue(true);
    }

    public function test_auto_attendance_catches_exception_without_throwing(): void
    {
        $user = $this->makeUser(1);

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_enabled', false)
            ->andReturn(true);

        $this->attendanceService->shouldReceive('canCheckIn')
            ->with(1)
            ->andReturn(true);

        $this->settingsService->shouldReceive('getSetting')
            ->with('auto_attendance_greeting', '')
            ->andReturn('인사');

        $this->attendanceService->shouldReceive('checkIn')
            ->once()
            ->andThrow(new \RuntimeException('출석 가능 시간이 아닙니다.'));

        // Mock the Log facade
        \Illuminate\Support\Facades\Log::shouldReceive('warning')
            ->once()
            ->with('Auto attendance failed', Mockery::type('array'));

        // Should not throw — exception is caught internally
        $this->listener->onLogin($user);

        $this->assertTrue(true);
    }
}
