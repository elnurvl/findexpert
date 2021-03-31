<?php

namespace Tests\Unit;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_user_can_have_many_topics()
    {
        // Arrange
        $user = User::factory()->create();
        $topics = Topic::factory()->count(3)->create([
            'user_id' => $user
        ]);
        // Act

        // Assert
        $this->assertInstanceOf(Collection::class, $user->topics);
        $this->assertCount(3, $user->topics);
        $this->assertInstanceOf(Topic::class, $user->topics[0]);
    }

    public function test_user_can_have_many_friends()
    {
        // Arrange
        $user = User::factory()->create();
        $friends = User::factory()->count(3)->create();
        $user->friends()->attach($friends);
        // Act

        // Assert
        $this->assertInstanceOf(Collection::class, $user->friends);
        $this->assertInstanceOf(User::class, $user->friends[0]);
        $this->assertCount(3, $user->friends);
    }
}
