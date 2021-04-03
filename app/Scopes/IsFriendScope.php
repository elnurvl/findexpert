<?php

namespace App\Scopes;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class IsFriendScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        $builder->addSelect([
            'is_friend' => DB::table('friendships')
                ->selectRaw("COUNT(*)")
                ->where('user_id', auth()->id())
                ->whereColumn('friend_id', 'users.id')
                ->limit(1)
        ]);
    }
}
