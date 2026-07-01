<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
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
        $bookId = $this->route('book');

        $isbnRules = ['required', 'max:13'];

        if ($this->isMethod('post')) {
            $isbnRules[] = Rule::unique('books', 'isbn');
        } else {
            $isbnRules[] = Rule::unique('books', 'isbn')->ignore($bookId);
        }

        return [
            'title' => ['required','max:255' ],
            'author' => ['required','max:255'],
            'isbn' => $isbnRules,
            'published_date' => ['required'],
            'genres' => ['required'],
            'image_url' => ['nullable', 'url'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名を入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.required' => 'ISBNを入力してください。',
            'isbn.max' => 'ISBNは13桁で入力してください。',
            'isbn.unique' => '入力されたISBNは重複したものが存在します。',
            'published_date.required' => '出版日を指定くしてください。',
            'genres.required' => 'ジャンルを一つ以上選択してください。',
            'image_url.url' => '画像はURL形式で入力してください。',
        ];
    }
}
