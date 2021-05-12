<?php

namespace App\Models;

use App\Scopes\IsFriendScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'website',
        'failed_to_reach',
        'no_topic',
        'shortening'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'failed_to_reach' => 'boolean',
        'no_topic' => 'boolean',
        'is_friend' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new IsFriendScope);
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id');
    }

    public function scopeWithIsFriend(Builder $builder)
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
