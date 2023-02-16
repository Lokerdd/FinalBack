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
      ->get()
      ->map(function ($item) {
        if ($item->image)
          $item->image = asset($item->image);
        return $item;
      });
  }

  public function store(Request $request) {
    $validated = Validator::make($request->all(), [
      "header" => 'required|string|max:255',
      "description" => 'required|string',
      "tags" => 'string',
      "image" => 'file|mimes:png,jpg,jpeg,svg'
    ]);
    if ($validated->fails()) {
      return response("{ 'error': 'Not valid data' }", Response::HTTP_BAD_REQUEST);
    }

    $post = new Post;

    $post->user_id = Auth::user()->id;
    $post->header = $request->header;
    $post->description = $request->description;

    if ($request->hasFile('image')) {
      $post->image = $request->image
        ->storeAs(
          'images/posts', 
          date('d-m-y_H-i').'.'.$request->image->extension(),
          'root_public'
        );
    }

    $post->save();

    if ($request->tags) {
      $tags = [];
      foreach (explode(' ', $request->tags) as $key) {
        if (!($tag = Tag::where('name', $key)->first())) {
          $tag = new Tag();
          $tag->name = $key;
        }

        $tags[] = $tag;
      }
      $post->tags()->saveMany($tags);
      $post->tags = $post->tags;
    }

    if ($request->hasFile('image')) {
      $post->image = asset($post->image);
    }

    return response()->json($post, Response::HTTP_OK);
  }
}
