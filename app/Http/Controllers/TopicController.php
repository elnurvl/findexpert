<?php

namespace App\Http\Controllers;

use App\Http\Resources\TopicResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Laudis\Neo4j\Client;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return ResourceCollection
     */
    public function index(User $user): ResourceCollection
    {
        return TopicResource::collection($user->topics);
    }

    /**
     * @param Client $neo4jClient
     * @param Request $request
     * @return ResourceCollection
     */
    public function search(Client $neo4jClient, Request $request): ResourceCollection
    {
        $request->validate([
            'q' => 'required'
        ]);
        // Split the search query
        $keywords = explode(" ", $request['q']);

        $users = User::with('topics')
            // Exclude friends
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('friendships')->where('friend_id', auth()->id());
            })
            // It is a bidirectional relationship
            ->whereNotIn('id', function ($query) {
                $query->select('friend_id')->from('friendships')->where('user_id', auth()->id());
            })
            // Filter users with topics relevant to the search
            ->whereIn('id', function ($query) use ($keywords) {
                $query = $query->select('user_id')->from('topics');

                // Match of one word is enough
                foreach ($keywords as $word) {
                    $query = $query->orWhere('content', 'like', '%'.$word.'%');
                }
                $query->distinct();
            })->get();

        // Eager load user networks
        $authId = auth()->id();
        $userIds = $users->except($authId)->pluck('id')->toArray();
        $result = $neo4jClient->run(<<<'CYPHER'
MATCH (a:User{id: $authId}), (u:User)
WHERE u.id IN $userIds
MATCH p = shortestPath((a)-[:KNOWS*]-(u))
RETURN nodes(p)
CYPHER,
            ['authId' => $authId, 'userIds' => $userIds]);

        // A single network is a list of user IDs representing the path from the auth user to the specified user
        // Example: If user 1 knows user 3 via user 2 the network would be [1, 2, 3]
        // The variable is list of networks of the auth user with each of the user in the search results
        $networks = collect();
        foreach ($result as $userId) {
            $networks->add($userId->get('nodes(p)'));
        }

        // Load all the relevant users from the database in advance
        // The users in the network stacks are not necessarily contained in the first users list
        $networkUsers = User::findMany($networks->flatten());

        foreach ($networks as $i => $network) {
            // The querying user is always in the end of the network stack
            $targetUserId = $network[array_key_last($network)]['id'];

            // Continue only if the querying user is connected to the auth user
            if (in_array($targetUserId, $userIds) && $network[0] != $authId) {
                $users->find($targetUserId)->network = collect();

                // Assign network to the queried users
                foreach ($network as $j => $userId) {
                    $users->find($targetUserId)->network->add($networkUsers->find($userId['id']));
                }
            }
        }

        return UserResource::collection($users);
    }
}
