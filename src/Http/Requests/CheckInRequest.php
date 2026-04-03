<?php

namespace Modules\Lastorder\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
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
            'greeting' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * 검증 오류 메시지
     */
    public function messages(): array
    {
        return [
            'greeting.max' => __('lastorder-attendance::messages.validation.greeting_max'),
        ];
    }
}
