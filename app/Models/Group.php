<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RobloxUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    public function robloxUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            RobloxUser::class,
            'group_roblox_user',
            'group_id',
            'roblox_user_id',
            'group_id',
            'roblox_user_id'
        );
    }

}
