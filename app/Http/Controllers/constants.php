<?php

namespace Constants;

class ValidationSchemas {
  public const login = [
    'email' => ['required', 'email', 'exists:users'],
    'password' => ['required']
  ];

  public const register = [
    'name' => ['required', 'min:4'],
    'email' => ['required', 'email', 'unique:users'],
    'password' => ['required', 'min:8']
  ];

  public const storePost = [
    "header" => 'required|string|max:255',
    "description" => 'required|string',
    "image" => 'file|mimes:png,jpg,jpeg,svg'
  ];

  public const updateUser = [
    "name" => 'string|max:255',
    "image" => 'file|mimes:png,jpg,jpeg,svg'
  ];
  
}