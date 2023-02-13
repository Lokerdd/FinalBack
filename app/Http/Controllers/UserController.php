<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function show(User $user)
    {
      $posts = Post::with(['tags:name'])
        ->where('user_id', $user->id)
        ->orderBy('id', 'desc')
        ->get()
      ;
      $user['posts'] = $posts;
      return $user;
    }
}