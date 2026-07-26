<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_login_user()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => "password",
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_user_validate_email()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => "",
            'password' => "password",
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_login_user_validate_password()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => "yamada@example.com",
            'password' => "",
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_login_user_validate_mismatch()
    {
        $user = User::find(1);

        $response = $this->post('/login', [
            'email' => "yamada@example.com",
            'password' => "pasword",
        ]);

        $response->assertSessionHasErrors([
            'email' => "認証に失敗しました。",
        ]);
    }
}
