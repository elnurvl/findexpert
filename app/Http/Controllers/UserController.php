<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

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
     * @param User $user
     * @return Response
     */
    public function addFriend(User $user): Response
    {
        if (auth()->user()->friends()->get()->contains($user)) return response('', 400);

        auth()->user()->friends()->attach($user);
        $user->friends()->attach(auth()->user());

        return response('');
    }
}
