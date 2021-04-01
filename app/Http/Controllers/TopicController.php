<?php

namespace App\Http\Controllers;

use App\Http\Resources\TopicResource;
use App\Models\User;
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
}
