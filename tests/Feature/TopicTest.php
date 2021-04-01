<?php

namespace Tests\Feature;

use App\Http\Resources\TopicResource;
use App\Http\Resources\UserResource;
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

    public function test_search_topic_should_list_unknown_users_relevant_to_the_keywords()
    {
        // Arrange
        $users = User::factory()->count(11)->create();

        $users[0]->topics()->save(Topic::make(['content' => 'Software engineering', 'tag' => 'h1']));
        $users[1]->topics()->save(Topic::make(['content' => 'Software development', 'tag' => 'h1']));
        $users[1]->topics()->save(Topic::make(['content' => 'Web development', 'tag' => 'h1']));
        $users[2]->topics()->save(Topic::make(['content' => 'Software', 'tag' => 'h1']));
        $users[3]->topics()->save(Topic::make(['content' => 'Gaming', 'tag' => 'h1']));
        $users[4]->topics()->save(Topic::make(['content' => 'Physics', 'tag' => 'h1']));
        $users[5]->topics()->save(Topic::make(['content' => 'Medicine', 'tag' => 'h1']));
        $users[6]->topics()->save(Topic::make(['content' => 'software', 'tag' => 'h1']));
        $users[7]->topics()->save(Topic::make(['content' => 'development', 'tag' => 'h1']));

        // Let's make eleventh and third users friend
        $users[10]->friends()->attach($users[2]);
        $users[2]->friends()->attach($users[10]);

        // Let's make eighth and eleventh users friend
        $users[10]->friends()->attach($users[7]);
        $users[7]->friends()->attach($users[10]);

        // Act
        $unauthorizedResponse = $this->getJson('api/topics/search?q=Software development');
        $badResponse = $this->actingAs($users[10])->getJson('api/topics/search');
        $response = $this->actingAs($users[10])->getJson('api/topics/search?q=Software development');

        // Assert
        $unauthorizedResponse->assertUnauthorized();
        $badResponse->assertStatus(422);
        $response->assertOk()->assertExactJson(UserResource::collection(collect([$users[0], $users[1], $users[6]]))->resolve());
    }
}
