<?php

namespace Tests\Feature;

use App\Listeners\PullHeadings;
use App\Listeners\ShortenUrl;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        // Arrange
        Event::fake();
        $url = 'http://devadamlar.com';

        // Act
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'website' => $url,
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        // Assert
        Event::assertListening(Registered::class, ShortenUrl::class);
        Event::assertListening(Registered::class, PullHeadings::class);
        $this->assertAuthenticated();
        $this->assertEquals($url, Auth::user()->website);
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
