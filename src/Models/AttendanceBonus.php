<?php

namespace Modules\Lastorder\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Lastorder\Attendance\Enums\BonusType;

class AttendanceBonus extends Model
{
    protected $table = 'attendance_bonuses';

    /**
     * created_at만 사용하고 updated_at은 사용하지 않음
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'bonus_type',
        'bonus_point',
        'bonus_date',
        'description',
        'created_at',
    ];

    protected $casts = [
        'bonus_type' => BonusType::class,
        'bonus_point' => 'integer',
        'bonus_date' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * 보너스를 받은 회원
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * 연관된 출석 기록
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
