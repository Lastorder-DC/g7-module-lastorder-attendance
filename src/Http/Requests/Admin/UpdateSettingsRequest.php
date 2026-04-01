<?php

namespace Modules\Lastorder\Attendance\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * 인증 여부 확인
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 유효성 검사 규칙
     */
    public function rules(): array
    {
        return [
            'base_point' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'allowed_start_time' => ['sometimes', 'date_format:H:i'],
            'allowed_end_time' => ['sometimes', 'date_format:H:i'],
            'auto_attendance_enabled' => ['sometimes', 'boolean'],
            'auto_attendance_greeting' => ['sometimes', 'string', 'max:200'],
            'rank_1_bonus' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'rank_2_bonus' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'rank_3_bonus' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'weekly_bonus' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'monthly_bonus' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'yearly_bonus' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'random_point_enabled' => ['sometimes', 'boolean'],
            'random_point_min' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'random_point_max' => ['sometimes', 'integer', 'min:0', 'max:100000', 'gte:random_point_min'],
            'random_point_chance' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'default_greetings' => ['sometimes', 'array'],
            'default_greetings.*' => ['string', 'max:200'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * 사용자 정의 에러 메시지
     */
    public function messages(): array
    {
        return [
            'base_point.integer' => '기본 포인트는 정수여야 합니다.',
            'base_point.min' => '기본 포인트는 0 이상이어야 합니다.',
            'allowed_start_time.date_format' => '출석 시작 시간 형식이 올바르지 않습니다. (HH:MM)',
            'allowed_end_time.date_format' => '출석 종료 시간 형식이 올바르지 않습니다. (HH:MM)',
            'random_point_chance.max' => '랜덤 포인트 확률은 100% 이하여야 합니다.',
            'random_point_max.gte' => '랜덤 포인트 최대값은 최소값 이상이어야 합니다.',
            'per_page.min' => '페이지당 항목 수는 1 이상이어야 합니다.',
            'per_page.max' => '페이지당 항목 수는 100 이하여야 합니다.',
        ];
    }
}
