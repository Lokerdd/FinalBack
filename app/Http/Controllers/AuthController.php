<?php

namespace App\Http\Controllers;

use Validator;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

use Helpers\AuthHelper;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'email' => ['required', 'email', 'exists:users'],
      'password' => ['required']
    ]);
    if ($validator->fails()) {
      return response()->json([
        'message' => $validator->getMessageBag()
      ], Response::HTTP_BAD_REQUEST);
    }

    if (Auth::attempt($request->only('email', 'password'))) {
      return AuthHelper::createToken();
    }
    
    return response()->json([
      'message' => 'The provided credentials do not match our records.',
    ], Response::HTTP_BAD_REQUEST);
  }

  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => ['required', 'min:4'],
      'email' => ['required', 'email', 'unique:users'],
      'password' => ['required', 'min:8']
    ]);
    if ($validator->fails()) {
      return response()->json([
        'message' => $validator->getMessageBag()
      ], Response::HTTP_BAD_REQUEST);
    }

    $user = User::create(array_merge(
      $request->only('name', 'email'),
      ['password' => bcrypt($request->password)]
    ));
    $user->save();

    Auth::attempt($request->only('email', 'password'));
    return AuthHelper::createToken();
  }

  public function logout(Request $request) {
    Auth::logout();

    return response()->json([
      'message' => 'Successfully logged out'
    ], Response::HTTP_OK);
  }

  public function getUserByToken() {
    if ($user = Auth::user()) {
      return response()->json([
        "user" => $user
      ], Response::HTTP_OK);
    }
    return response()->json([
      "message" => "You aren't authorized"
    ], Response::HTTP_UNAUTHORIZED);

  }
}
