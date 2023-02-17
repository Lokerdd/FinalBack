<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Constants\ValidationSchemas;

define('storage', Storage::disk('root_public'));

class UserController extends Controller
{
    public function show($id) {
      $user = User::findOrFail($id);
      $posts = Post::with(['tags:name'])
        ->where('user_id', $user->id)
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($item) {
          if ($item->image)
            $item->image = asset($item->image);
          return $item;
        });
      ;
      $user['posts'] = $posts;
      if ($user->avatar) {
        $user->avatar = asset($user->avatar);
      }
      return $user;
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
      ['name' => $name, 'avatar' => $avatar] = $request;
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
