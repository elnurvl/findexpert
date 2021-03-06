<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        if (!in_array('web', config('fortify.middleware'))) {
            $this->markTestSkipped('Fortify uses the api middleware');
        }

        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        if (!in_array('web', config('fortify.middleware'))) {
            $this->markTestSkipped('Fortify uses the api middleware');
        }

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_authenticate_using_the_api()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->post('/api/sanctum/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Phpunit'
        ]);

        // Assert
        $response->assertOk();
    }
}
