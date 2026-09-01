<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required','string','max:1000'],
        ];
    }

    public function messages()
    {
        return [
            'rating.required' => '評価を5段階で選択してください。',
            'rating.min' => '最小1以上になるように数字を選択して下さい',
            'rating.max' => '最大5以下になるように数値を選択して下さい。',
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは1000文字以内で入力してください。',
        ];
    }
}
