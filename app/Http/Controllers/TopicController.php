<?php

namespace App\Http\Controllers;

use App\Http\Resources\TopicResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

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
     * @param Request $request
     * @return ResourceCollection
     */
    public function search(Request $request): ResourceCollection
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
            });

        return UserResource::collection($users->get());
    }
}
