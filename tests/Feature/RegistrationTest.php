<?php

namespace Tests\Feature;

use App\Listeners\PullHeadings;
use App\Listeners\ShortenUrl;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function websiteProvider()
    {
        return [
            ['http://devadamlar.com'],
            [null]
        ];
    }

    public function test_registration_screen_can_be_rendered()
    {
        if (!in_array('web', config('fortify.middleware'))) {
            $this->markTestSkipped('Fortify uses the api middleware');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * @dataProvider websiteProvider
     * @param string|null $website
     */
    public function test_new_users_can_register(?string $website)
    {
        // Arrange
        Event::fake();

        $body = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ];

        if ($website != null) {
            $body['website'] = $website;
        }

        // Act
        $response = $this->postJson('/register', $body);



        // Assert
        Event::assertListening(Registered::class, ShortenUrl::class);
        Event::assertListening(Registered::class, PullHeadings::class);

        $response->assertCreated();
        $this->assertAuthenticated();
        $this->assertEquals($website, Auth::user()->website);

        $result = $this->neo4jClient->run(<<<'CYPHER'
MATCH (u:User)
WHERE u.id = $id
RETURN u
CYPHER,
            ['id' => auth()->id()]
        );
        $this->assertNotEmpty($result);
    }

    public function test_website_should_be_valid_url()
    {
        // Arrange
        Event::fake();
        $url = 'invalid url';

        // Act
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'website' => $url,
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        // Assert

        $response->assertStatus(422);
    }
}
