<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenreRequest extends FormRequest
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
        $genreId = $this->route('genre') ?? $this->route('id');

        $genreRules = ['required','string','max:255'];

        if ($this->isMethod('post')) {
            $genreRules[] = Rule::unique('genres', 'name');
        } else {
            $genreRules[] = Rule::unique('genres', 'name')->ignore($genreId);
        }

        return [
            'name' => $genreRules,
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'ジャンル名を入力してください。',
            'name.max' => 'ジャンル名は255文字以内で入力してください。',
            'name.unique' => 'ジャンル名が重複したものが存在します。'
        ];
    }
}
