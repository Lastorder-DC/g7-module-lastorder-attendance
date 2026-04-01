<?php

namespace Modules\Lastorder\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Lastorder\Attendance\Database\Factories\AttendanceFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attendance_date',
        'attendance_time',
        'greeting',
        'base_point',
        'random_point',
        'total_point',
        'daily_rank',
        'consecutive_days',
        'total_days',
        'ip_address',
        'is_auto',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_auto' => 'boolean',
        'base_point' => 'integer',
        'random_point' => 'integer',
        'total_point' => 'integer',
        'daily_rank' => 'integer',
        'consecutive_days' => 'integer',
        'total_days' => 'integer',
    ];

    /**
     * 출석한 회원
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * 해당 출석에 대한 보너스 기록
     */
    public function bonuses(): HasMany
    {
        return $this->hasMany(AttendanceBonus::class);
    }

    /**
     * 모델 팩토리 생성
     */
    protected static function newFactory(): AttendanceFactory
    {
        return AttendanceFactory::new();
    }
}
