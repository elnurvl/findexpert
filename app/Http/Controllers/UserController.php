<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laudis\Neo4j\Client;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        $users = User::withCount('friends')->paginate(50);

        return UserResource::collection($users);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * List friends of the user
     * @param User $user
     * @return ResourceCollection
     */
    public function getFriends(User $user): ResourceCollection
    {
        return UserResource::collection($user->friends()->withCount('friends')->paginate(50));
    }

    /**
     * Declare a user as a friend
     * @param Client $neo4jClient
     * @param User $user
     * @return Response
     */
    public function addFriend(Client $neo4jClient, User $user): Response
    {
        if (auth()->user()->friends()->get()->contains($user)) return response('', 400);

        $authId = auth()->id();

        auth()->user()->friends()->attach($user);
        $user->friends()->attach(auth()->user());

        DB::transaction(function () use ($authId, $user, $neo4jClient) {
            // TODO: A temporary workaround. Use the library method instead of this request.
            Http::withBasicAuth(env('NEO4J_USER'), env('NEO4J_PASSWORD'))
                ->post(env('NEO4J_PROTOCOL').'://'.env('NEO4J_HOST').'/db/data/transaction/commit', [
                    'statements' => [
                        [
                            'statement' => <<<'CYPHER'
MATCH (a:User{id: $authId), (u:User{id: $userId})
MERGE (a)-[:KNOWS]->(u)
MERGE (u)-[:KNOWS]->(a)
CYPHER,
                            'parameters' => [
                                'authId' => $authId,
                                'userId' => $user->id
                            ]
                        ]
                    ]
                ]);

            // TODO: Use this instead of the above request.
            // TODO: Same query does not work with the library method. Possibly a library bug.
//            $neo4jClient->run(<<<'CYPHER'
//MATCH (a:User{id: $authId}), (u:User{id: $userId})
//MERGE (a)-[:KNOWS]->(u)
//MERGE (u)-[:KNOWS]->(a)
//CYPHER,
//                ['authId' => $authId, 'userId' => $user->id]
//            );
        });

        return response('');
    }

    /**
     * @param Client $neo4jClient
     * @param int $userId
     * @return ResourceCollection the list of users from the auth user to a user taking the shortest path
     */
    public function network(Client $neo4jClient, int $userId): ResourceCollection
    {
        $authId = auth()->id();
        $userIds = collect();

        $result = $neo4jClient->run(<<<'CYPHER'
MATCH (a:User{id: $authId}), (u:User{id: $userId}),
p = shortestPath((a)-[:KNOWS*]-(u))
RETURN nodes(p)
CYPHER,
            ['authId' => $authId, 'userId' => $userId]
        );
        foreach ($result as $userId) {
            $userIds->add($userId->get('nodes(p)'));
        }

        return UserResource::collection(User::findMany($userIds[0]));
    }
}
