<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
      return Post::with(['user:id,name,email', 'tags:name'])->orderBy('id', 'desc')->get();
    }
}
