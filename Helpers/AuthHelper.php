<?php

namespace Helpers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthHelper {
  public static function createToken() {
    $token = Auth::user()
      ->createToken(config('app.name'))
      ->accessToken;

    return response()->json([
      'user' => Auth::user(),
      'token' => $token,
    ], Response::HTTP_OK);
  }
}