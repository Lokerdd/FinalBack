<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index() {
      return Post::with(['user:id,name,email', 'tags:name'])
        ->orderBy('id', 'desc')
        ->get();
    }

    public function store(Request $request) {
      $validated = Validator::make($request->all(), [
        "header" => 'required|string|max:255',
        "description" => 'required|string'
      ]);
      if ($validated->fails()) {
        return response("{ 'error': 'Not valid data' }", Response::HTTP_BAD_REQUEST);
      }

      $post = new Post;

      $post->user_id = Auth::user()->id;
      $post->header = $request->input('header');
      $post->description = $request->input('description');

      $post->save();

      return response()->json($post, Response::HTTP_OK);
    }
}
