<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReadingPlanRequest extends FormRequest
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
        $rules = [
            'target_date' => ['required', 'after_or_equal:today'],
        ];
        //postの時のみ適応
        if ($this->isMethod('post')) {
            $rules['book_id'] = ['required'];
        }
        //戻り値を配列型に変える。
        return $rules;
    }

    public function messages()
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'target_date.required' => '日付を登録してください。',
            'target_date.after_or_equal' => '日付は今日以降の日付を入力してください。',
        ];
    }
}
