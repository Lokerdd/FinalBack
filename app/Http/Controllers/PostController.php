<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
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
      "description" => 'required|string',
      "tags" => 'array'
    ]);
    if ($validated->fails()) {
      return response("{ 'error': 'Not valid data' }", Response::HTTP_BAD_REQUEST);
    }

    $post = new Post;

    $post->user_id = Auth::user()->id;
    $post->header = $request->input('header');
    $post->description = $request->input('description');

    $post->save();

    $tags = [];
    foreach ($request->input('tags') as $key) {
      if (!($tag = Tag::where('name', $key)->first())) {
        $tag = new Tag();
        $tag->name = $key;
      }

      $tags[] = $tag;
    }
    $post->tags()->saveMany($tags);
    $post->tags = $post->tags;

    return response()->json($post, Response::HTTP_OK);
  }
}
