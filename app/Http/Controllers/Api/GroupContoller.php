<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
class GroupContoller extends Controller
{
    public function index(){
        $groups = Group::all();
        return response()->json($groups);
    }
}
