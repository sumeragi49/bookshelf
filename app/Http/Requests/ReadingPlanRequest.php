<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

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
        //$rules = [
            //'target_date' => ['required', 'after_or_equal:today'],
        //];
        //postの時のみ適応
        //if ($this->isMethod('post')) {
            //$rules['book_id'] = ['required', 'integer','exists'];
        //}
        //戻り値を配列型に変える。
        //return $rules;

        //{book},{id}のどちらでもURL末尾の数値を抽出
        $planId = $this->route('plan') ?? $this->route('id');

        $planRules = ['required','integer'];

        if ($this->isMethod('post')) {
            $planRules[] = Rule::exists('reading_plan', 'id');
        } else {
            $planRules[] = Rule::exists('reading_plan', 'id')->ignore($planId);
        }

        $uniqueRule = Rule::unique('reading_plan')->where(function (Builder $query) {
            return $query 
                -> where('user_id', auth()->id())
                -> where('book_id', $this->book_id)
                -> where('status', 'in_progress');
        });

        $planRules[] = $uniqueRule;

         return [
            'book_id' => $planRules,
            'target_date' => ['required', 'date', 'after_or_equal:today'],
            'genres' => ['required', 'array'],
            'image_url' => ['nullable', 'url'],
        ];
    }

    public function messages()
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.unique' => 'この書籍は既に進行中の読書計画が存在します。',
            'target_date.required' => '期日は必須です。',
            'target_date.date' => '期日は有効な日付で形式で入力してください。',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください。',
        ];
    }
}
