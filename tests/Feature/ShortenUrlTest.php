<?php

namespace Tests\Feature;

use App\Listeners\ShortenUrl;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use TiMacDonald\Log\LogFake;

class ShortenUrlTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @var ShortenUrl
     */
    private $listener;

    protected function setUp(): void
    {
        parent::setUp();
        Log::swap(new LogFake);
        $this->listener = new ShortenUrl(10);
    }

    public function userProvider(): array
    {
        return [
            ['http://devadamlar.com', 'http://cutt.ly/dev', 7],
            ['http://cutt.ly/already-shortened', 'http://cutt.ly/already-shortened' , 1],
            ['invalid_url', null, 2],
            ['http://devadamlar.com', null, 4]
        ];
    }

    /**
     * @dataProvider userProvider
     * @param string $website
     * @param string|null $shortening
     * @param int $status 7 = successful, 1 = already shortened url, 4 = invalid API key, 2, 5 = the link is not valid
     */
    public function test_should_save_shortened_url_in_user_profile(string $website, ?string $shortening, int $status)
    {
        // Arrange
        $user = User::factory()->create([
            'website' => $website
        ]);
        Http::fake([
            'https://cutt.ly/api/api.php*' => Http::response([
                'url' => [
                    'status' => $status,
                    'fullLink' => $user->website,
                    'shortLink' => 'http://cutt.ly/dev',
                ]
            ], 200),
        ]);

        // Act
        $this->listener->handle(new Registered($user));

        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'shortening' => $shortening
        ]);
        if ($status == 4) {
            Log::assertLogged('error');
        }

    }

    public function test_log_if_api_request_failed()
    {
        // Arrange
        $user = User::factory()->create();
        Http::fake([
            '*' => Http::response("", 500)
        ]);

        // Act
        $this->listener->handle(new Registered($user));

        // Assert
        Log::assertLogged('error');
    }
}
