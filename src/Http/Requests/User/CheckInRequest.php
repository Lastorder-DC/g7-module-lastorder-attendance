<?php

namespace Modules\Lastorder\Attendance\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
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
            'greeting' => ['required', 'string', 'max:200'],
        ];
    }

    /**
     * 입력값 전처리 (HTML 태그 제거)
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('greeting')) {
            $this->merge([
                'greeting' => strip_tags($this->input('greeting')),
            ]);
        }
    }

    /**
     * 사용자 정의 에러 메시지
     */
    public function messages(): array
    {
        return [
            'greeting.required' => '인삿말을 입력해 주세요.',
            'greeting.string' => '인삿말은 문자열이어야 합니다.',
            'greeting.max' => '인삿말은 최대 200자까지 입력할 수 있습니다.',
        ];
    }
}
