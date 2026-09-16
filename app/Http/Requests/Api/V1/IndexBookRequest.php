<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
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

    /*
    *バリデーション前のデータの整形
    */
    protected function prepareForValidation(): void
    {
        if (!$this->has('per_page')) {
            $this->merge([
                'per_page' => 20,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages()
    {
        return [
            'keyword.max' => 'キーワードは255文字以内で入力してください。',
            'genre_id.integer' => 'ジャンルIDは整数で指定してください。',
            'genre_id.exists' => '指定されたジャンルが存在しません。',
            'per_page.integer' => '1ページあたりの取得件数は整数で指定してください。',
            'per_page.min' => '1ページあたりのページ数は最低1件以上で指定してください。',
            'per_page.max' => '1ページあたりのページ数は最大100件以内で指定してください。'
        ];
    }
}
