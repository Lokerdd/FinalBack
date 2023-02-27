<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

use Constants\ValidationSchemas;

define('storage', Storage::disk('root_public'));
define('TAGS', 'Tags');
define('AUTHOR', 'Author');
define('POSTS_ON_PAGE', 6);

class UserController extends Controller
{
  private static function sortPosts($user, $posts) {
    $amountOfPages = ceil($posts->count() / POSTS_ON_PAGE);
    $posts = $posts
      ->orderBy('id', 'desc')
      ->paginate(POSTS_ON_PAGE)
      ->map(function ($item) {
        if ($item->image)
          $item->image = asset($item->image);
        return $item;
      });
    $user['posts'] = $posts;
    $user['pages'] = $amountOfPages;
    return $user;
  }

  public function show($id, Request $request) {
    $user = User::findOrFail($id);
    $searchText = $request->query('search-text');
    $filter = $request->query('filter');
    
    if ($user->avatar) {
      $user->avatar = asset($user->avatar);
    }

    $postsSortedByTags = Post::with(['tags:name'])
      ->whereHas('tags', 
        function(Builder $item) use($searchText) {
          $item->where('name', 'like' , '%'.$searchText.'%');
        }
      )
      ->where('user_id', $id);
    if ($filter === TAGS) return self::sortPosts($user, $postsSortedByTags);

    $posts = Post::with(['tags:name'])
      ->where('description', 'like', '%'.$searchText.'%')
      ->orWhere('header', 'like', '%'.$searchText.'%')
      ->where('user_id', $id)
      ->union($postsSortedByTags);

    return self::sortPosts($user, $posts);
  }

  public function update(Request $request) {
    $validated = Validator::make(
      $request->all(), 
      ValidationSchemas::updateUser
    );
    if ($validated->fails()) {
      return response()->json([
        'message' => 'Not valid data'
        ], 
        Response::HTTP_BAD_REQUEST
      );
    }
    ['name' => $name, 'image' => $avatar] = $request;
    if (!($name || $avatar)) {
      return response()->json([
        'message' => 'Nothing to change'
        ], 
        Response::HTTP_BAD_REQUEST
      );
    }

    $user = Auth::user();
    if ($name) $user->name = $name;
    if ($avatar) {
      if (
        $user->avatar 
        && storage->exists($user->avatar)
      ) storage->delete($user->avatar);
      $user->avatar = $avatar
        ->storeAs(
          'images/avatars',
          date('d|m|y_H:i:s').'.'.$avatar->extension(),
          'root_public'
        );
    }

    $user->save();

    if ($user->avatar) {
      $user->avatar = asset($user->avatar);
    }
    return $user;
  }
}
