<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $role= Role::where('name', 'Viewer')->first();
        $user->roles()->attach($role->id);

        $token = JWTAuth::fromUser($user);
        
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'roles'=>$role->name,
            'permissions'=>$role->permissions->pluck('name')
        ], 201);
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $role=JWTAuth::user()->roles()->with('permissions')->get();
        $role_names = $role->pluck('name')->implode(',');
        $permissions=$role->pluck('permissions')->flatten()->pluck('name');

        return response()->json([
            'message' => 'Login successful',
            'user'=>JWTAuth::user(),
            'token' => $token,
            'roles'=>$role_names,
            'permissions'=>$permissions
        ]);
    }
    
    public function user()
    {
        $role=JWTAuth::user()->roles()->with('permissions')->get();
        $role_names = $role->pluck('name')->implode(',');
        $permissions=$role->pluck('permissions')->flatten()->pluck('name');
        
        // Get token from request header or use JWT facade
        $token = request()->bearerToken() ?: (JWTAuth::getToken() ? JWTAuth::getToken()->get() : null);
            
        return response()->json([
            'user'=>JWTAuth::user(),
            'token'=>$token,
            'roles'=>$role_names,
            'permissions'=>$permissions,
           // 'req'=>JWTAuth::getToken()->get(),
           
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'token' => JWTAuth::refresh(),
        ]);
    }

    // Debug method to test token retrieval
    public function debugToken(Request $request)
    {
        return response()->json([
            'bearer_token' => $request->bearerToken(),
            'authorization_header' => $request->header('Authorization'),
            'all_headers' => $request->headers->all(),
            'jwt_token_object' => JWTAuth::getToken(),
            'jwt_token_string' => JWTAuth::getToken() ? JWTAuth::getToken()->get() : null,
            'user' => JWTAuth::user(),
        ]);
    }
}
