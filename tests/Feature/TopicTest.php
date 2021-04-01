<?php

namespace Tests\Feature;

use App\Http\Resources\TopicResource;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_topics_should_list_topics_of_user()
    {
        // Arrange
        $user = User::factory()->create();
        $topics = Topic::factory()->count(5)->create([
            'user_id' => $user->id
        ]);

        // Act
        $unauthorizedResponse = $this->getJson("api/users/{$user->id}/topics");
        $response = $this->actingAs($user)->getJson("api/users/{$user->id}/topics");

        // Assert
        $unauthorizedResponse->assertUnauthorized();
        $response->assertOk()->assertJson(TopicResource::collection($topics)->resolve());
    }
}
