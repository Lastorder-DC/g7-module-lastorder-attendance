<?php

namespace Modules\Lastorder\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attended_at',
        'attended_time',
        'greeting',
        'base_point',
        'bonus_point',
        'random_point',
        'rank_point',
        'consecutive_point',
        'total_point',
        'daily_rank',
        'consecutive_days',
        'total_days',
        'ip_address',
        'is_auto',
    ];

    protected $casts = [
        'attended_at' => 'date',
        'base_point' => 'integer',
        'bonus_point' => 'integer',
        'random_point' => 'integer',
        'rank_point' => 'integer',
        'consecutive_point' => 'integer',
        'total_point' => 'integer',
        'daily_rank' => 'integer',
        'consecutive_days' => 'integer',
        'total_days' => 'integer',
        'is_auto' => 'boolean',
    ];

    /**
     * 출석한 회원
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
