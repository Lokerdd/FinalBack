<?php

namespace Helpers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthHelper {
  public static function createToken() {
    $token = Auth::user()->createToken(config('app.name'));
    $token->accessToken->expires_at = Carbon::now()->addDay();
    $token->accessToken->save();

    return response()->json([
      'user' => Auth::user(),
      'token_type' => 'Bearer',
      'token' => $token->accessToken,
      'expires_at' => 
        Carbon::parse($token->accessToken->expires_at)
          ->toDateTimeString()
    ], Response::HTTP_OK);
  }
}