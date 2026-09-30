<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $seed = true;

    public function test_report_index()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)->get(route('reports.index'));
        
        $response->assertStatus(200);
    }
    //guestによるレポートページへのアクセス不可の検証
    public function test_guest_cannot_access_report_index()
    {
        $response = $this->get(route('reports.index'));
        
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
