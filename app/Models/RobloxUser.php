<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class RobloxUser extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    public function groups(): BelongsToMany
{
    return $this->belongsToMany(Group::class, 'group_roblox_user', 'roblox_user_id', 'group_id');
}

}
