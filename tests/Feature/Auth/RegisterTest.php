<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_register_user()
    {
        $response = $this->post('/register', [
            'name' => "テスト太郎",
            'email' => "test1@example.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertRedirect(route('books.index'));
        //DBに正しく保存されたか検証
        $this->assertDatabaseHas(User::class, [
            'email' => "test1@example.com",
        ]);
    }

    public function test_register_user_validate_name()
    {
        $response = $this->post('/register', [
            'name' => "",
            'email' => "test1@example.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    public function test_register_user_validate_email()
    {
        $response = $this->post('/register', [
            'name' => "テスト太郎",
            'email' => "",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_register_user_validate_password8()
    {
        $response = $this->post('/register', [
            'name' => "テスト太郎",
            'email' => "test1@example.com",
            'password' => "pasword",
            'password_confirmation' => "pasword",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    public function test_register_user_validate_mismatch()
    {
        $response = $this->post('/register', [
            'name' => "テスト太郎",
            'email' => "test1@example.com",
            'password' => "password",
            'password_confirmation' => "password1",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    public function test_register_user_validate_password()
    {
        $response = $this->post('/register', [
            'name' => "テスト太郎",
            'email' => "test1@example.com",
            'password' => "",
            'password_confirmation' => "",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }
}
