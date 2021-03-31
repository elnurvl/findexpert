<?php

namespace Tests\Unit;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    public function test_topic_belongs_to_user()
    {
        // Arrange
        $user = User::factory()->create();
        $topic = Topic::factory()->create([
            'user_id' => $user
        ]);
        // Act

        // Assert
        $this->assertInstanceOf(User::class, $topic->user);
    }
}
