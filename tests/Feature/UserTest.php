<?php

namespace Tests\Feature;

use App\Http\Resources\UserResource;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_should_return_paginated_users_in_json()
    {
        // Arrange
        $users = User::factory()->count(5)->create();
        $users[0]->friends()->attach($users[1]);

        // Act
        $unauthorizedResponse = $this->getJson('/api/users');
        $response = $this->actingAs($users[1])->getJson('/api/users');

        // Assert
        $unauthorizedResponse->assertUnauthorized();
        $response->assertOk()->assertJson(['data' => UserResource::collection(User::withCount('friends')->get())->resolve()]);
        $this->assertEquals(1, $response['data'][0]['total_friends']);
    }

    public function test_show_should_return_specified_user()
    {
        // Arrange
        $users = User::factory()->count(2)->create();

        // Act
        $unauthorizedResponse = $this->getJson("api/users/{$users[1]->id}");
        $response = $this->actingAs($users[0])->getJson("api/users/{$users[1]->id}");

        // Assert
        $unauthorizedResponse->assertUnauthorized();
        $response->assertOk()->assertJson((new UserResource($users[1]))->resolve());
    }

    public function test_user_can_declare_another_user_as_friend()
    {
        // Arrange
        $users = User::factory()->count(2)->create();

        // Act
        $unauthorizedResponse = $this->postJson("api/users/{$users[1]->id}/add-friend");
        $response = $this->actingAs($users[0])->postJson("api/users/{$users[1]->id}/add-friend");
        $secondResponse = $this->actingAs($users[0])->postJson("api/users/{$users[1]->id}/add-friend");

        // Assert
        $unauthorizedResponse->assertUnauthorized();
        $response->assertOk();
        $secondResponse->assertStatus(400);
        $this->assertDatabaseHas('friendships', [
            'user_id' => $users[0]->id,
            'friend_id' => $users[1]->id
        ]);
        $this->assertDatabaseHas('friendships', [
            'user_id' => $users[1]->id,
            'friend_id' => $users[0]->id
        ]);
    }

    public function test_get_friends_should_list_friend_of_a_user()
    {
        // Arrange
        $user = User::factory()->create();
        $users = User::factory()->count(5)->create();
        $user->friends()->attach($users[0]);
        $user->friends()->attach($users[1]);
        $user->friends()->attach($users[2]);

        $users[0]->friends()->attach($user);

        // Act
        $unauthorizedResponse = $this->getJson("api/users/{$user->id}/friends");
        $response = $this->actingAs($user)->getJson("api/users/{$user->id}/friends");

        // Assert
        $unauthorizedResponse->assertUnauthorized();
        $response
            ->assertOk()
            ->assertJson(['data' => UserResource::collection($user->friends()->withCount('friends')->get())->resolve()]);
        $this->assertEquals(1, $response['data'][0]['total_friends']);

    }
}
