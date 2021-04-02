<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

function createUser(int $count = 1, array $attributes = [])
{
    $users = User::factory()->count($count)->create($attributes);

    // Create graph representations of the models
    Http::withBasicAuth(env('NEO4J_USER'), env('NEO4J_PASSWORD'))
        ->post(env('NEO4J_PROTOCOL').'://'.env('NEO4J_HOST').'/db/data/transaction/commit', [
        'statements' => [
            [
                'statement' => <<<'CYPHER'
FOREACH (id IN $ids | MERGE (:User{id: id}))
CYPHER,
                'parameters' => [
                    'ids' => $users->pluck('id')->toArray()
                ]
            ]
        ]
    ]);

    if ($count == 1) return $users[0];

    return $users;
}

function attachUser(User $user, User $friend)
{
    $user->friends()->attach($friend);

    // Create graph representations of the relationship
    Http::withBasicAuth(env('NEO4J_USER'), env('NEO4J_PASSWORD'))
        ->post(env('NEO4J_PROTOCOL').'://'.env('NEO4J_HOST').'/db/data/transaction/commit', [
        'statements' => [
            [
                'statement' => <<<'CYPHER'
MATCH (u:User{id: $userId}), (f:User{id: $friendId})
MERGE (u)-[:KNOWS]->(f)
CYPHER,
                'parameters' => [
                    'userId' => $user->id,
                    'friendId' => $friend->id
                ]
            ]
        ]
    ]);
}
