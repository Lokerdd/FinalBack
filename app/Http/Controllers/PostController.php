<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;

use Constants\ValidationSchemas;

define('TAGS', 'Tags');
define('AUTHOR', 'Author');
define('POSTS_ON_PAGE', 6);

class PostController extends Controller
{
  private static function sortPosts($posts) {
    $amountOfPages = ceil($posts->count() / POSTS_ON_PAGE);
    $posts = $posts
      ->orderBy('id', 'desc')
      ->paginate(POSTS_ON_PAGE)
      ->map(function ($item) {
        if ($item->image)
          $item->image = asset($item->image);
        return $item;
      });
    return [
      'posts' => $posts,
      'pages' => $amountOfPages
    ];
  }

  public function index(Request $request) {
    $searchText = $request->query('search-text');
    $filter = $request->query('filter');

    $postsSortedByTags = Post::with(['user:id,name,email', 'tags:name'])
      ->whereHas('tags', 
        function(Builder $item) use($searchText) {
          $item->where('name', 'like' , '%'.$searchText.'%');
        }
      );
    if ($filter === TAGS) return self::sortPosts($postsSortedByTags);

    $postsSortedByAuthor = Post::with(['user:id,name,email', 'tags:name'])
      ->whereHas('user',
        function(Builder $item) use($searchText) {
          $item->where('name', 'like' , '%'.$searchText.'%');
        }
      );
    if ($filter === AUTHOR) return self::sortPosts($postsSortedByAuthor);

    $result = Post::with(['user:id,name,email', 'tags:name'])
      ->where('description', 'like', '%'.$searchText.'%')
      ->orWhere('header', 'like', '%'.$searchText.'%')
      ->union($postsSortedByTags)
      ->union($postsSortedByAuthor);

    return self::sortPosts($result);
  }

  public function store(Request $request) {
    $validated = Validator::make(
      $request->all(), 
      ValidationSchemas::storePost
    );
    if ($validated->fails()) {
      return response()->json([
        'message' => 'Not valid data'
      ], Response::HTTP_BAD_REQUEST);
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
