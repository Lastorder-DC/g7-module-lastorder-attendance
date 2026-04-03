<?php

namespace Modules\Lastorder\Attendance\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * 인증/권한은 미들웨어에서 처리
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 요청에 적용할 검증 규칙
     */
    public function rules(): array
    {
        return [
            'basic.base_point' => ['sometimes', 'integer', 'min:0'],
            'basic.auto_attendance_enabled' => ['sometimes', 'boolean'],

            'time.time_restriction_enabled' => ['sometimes', 'boolean'],
            'time.start_hour' => ['sometimes', 'integer', 'min:0', 'max:23'],
            'time.end_hour' => ['sometimes', 'integer', 'min:1', 'max:24'],

            'bonus.rank_1st_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.rank_2nd_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.rank_3rd_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.consecutive_weekly_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.consecutive_monthly_point' => ['sometimes', 'integer', 'min:0'],
            'bonus.consecutive_yearly_point' => ['sometimes', 'integer', 'min:0'],

            'random.random_point_enabled' => ['sometimes', 'boolean'],
            'random.random_point_min' => ['sometimes', 'integer', 'min:0'],
            'random.random_point_max' => ['sometimes', 'integer', 'min:0'],

            'greetings.default_greetings' => ['sometimes', 'array'],
            'greetings.default_greetings.*' => ['string', 'max:200'],
        ];
    }

    /**
     * 검증 오류 메시지
     */
    public function messages(): array
    {
        return [
            'basic.base_point.min' => __('lastorder-attendance::messages.validation.base_point_min'),
            'time.start_hour.min' => __('lastorder-attendance::messages.validation.start_hour_min'),
            'time.start_hour.max' => __('lastorder-attendance::messages.validation.start_hour_max'),
            'time.end_hour.min' => __('lastorder-attendance::messages.validation.end_hour_min'),
            'time.end_hour.max' => __('lastorder-attendance::messages.validation.end_hour_max'),
            'greetings.default_greetings.*.max' => __('lastorder-attendance::messages.validation.greeting_max'),
        ];
    }
}
