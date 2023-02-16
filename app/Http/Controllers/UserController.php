<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
      return $user;
    }

    public function update(Request $request) {
      $validated = Validator::make($request->all(), [
        "name" => 'string|max:255',
        "image" => 'file|mimes:png,jpg,jpeg,svg'
      ]);
      if ($validated->fails()) {
        return response()->json([
          'message' => 'Not valid data'
          ], 
          Response::HTTP_BAD_REQUEST
        );
      }
      if (!($request->name || $request->avatar)) {
        return response()->json([
          'message' => 'Nothing to change'
          ], 
          Response::HTTP_BAD_REQUEST
        );
      }
      $user = Auth::user();
      if ($request->name) $user->name = $request->name;
      if ($request->hasFile('avatar')) {
        $user->avatar = $request->avatar
          ->storeAs(
            'images/avatars',
            $user->email.date('d-m-y_H-i').'.'.$request->avatar->extension(),
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
