<?php

namespace Tests\Feature;

use App\Listeners\PullHeadings;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPHtmlParser\Dom;
use Tests\TestCase;

class PullHeadingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    private $content;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private $user;
    /**
     * @var PullHeadings
     */
    private $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->content = Storage::disk(
            'local'
        )->get('mock_response.html');
        $this->user = User::factory()->create();
        $this->listener = new PullHeadings(new Dom(), 10);
    }

    public function test_should_parse_the_url_get_headings_and_save_in_database()
    {
        // Arrange
        Http::fake([
            '*' => Http::response($this->content, 200)
        ]);

        // Act
        $this->listener->handle(new Registered($this->user));

        // Assert
        $this->assertDatabaseHas('topics', [
            'user_id' => $this->user->id,
            'content' => 'Software engineering',
            'tag' => 'h1'
        ]);

        $this->assertDatabaseHas('topics', [
            'user_id' => $this->user->id,
            'content' => 'Mobile development',
            'tag' => 'h2'
        ]);

        $this->assertDatabaseHas('topics', [
            'user_id' => $this->user->id,
            'content' => 'Web development',
            'tag' => 'h2'
        ]);

        $this->assertDatabaseHas('topics', [
            'user_id' => $this->user->id,
            'content' => 'Flutter framework',
            'tag' => 'h3'
        ]);

        $this->assertDatabaseHas('topics', [
            'user_id' => $this->user->id,
            'content' => 'Laravel framework',
            'tag' => 'h3'
        ]);
    }

    public function test_remember_if_the_site_cannot_be_reached()
    {
        // Arrange
        Http::fake([
            '*' => Http::response(null, 500)
        ]);
        // Act
        $this->listener->handle(new Registered($this->user));
        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'failed_to_reach' => true
        ]);
    }

    public function test_remember_if_site_had_not_any_headings()
    {
        // Arrange
        Http::fake();
        // Act
        $this->listener->handle(new Registered($this->user));
        // Assert
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'no_topic' => true
        ]);
    }

    public function test_listener_is_not_executed_if_user_has_no_website()
    {
        // Arrange
        Http::fake();
        $this->user->website = null;

        // Act
        $this->listener->handle(new Registered($this->user));

        // Assert
        Http::assertNothingSent();
    }
}
