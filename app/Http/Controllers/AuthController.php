<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ValidatesRequests;

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|max:255',
            'password' => 'required'
        ]);

        $login = $request->only('email', 'password');

        if (!Auth::attempt($login)) {
            return response(['message' => 'Invalid login credential!!'], 401);
        }
        /**
         * @var User $user
         */
        $user = Auth::user();
        $token = $user->createToken($user->name);

        return response([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'accessToken' => $token->accessToken,
            'token_expires_at' => $token->token->expires_at,
            'roles' => $user->roles->pluck('name'),
        ], 200);
    }

    public function logout(Request $request)
    {
        /**
         * @var user $user
         */
        $user = Auth::user();
        // $user->tokens->each(function ($token) {
        //     $token->delete();
        // });
        // return response(['message' => 'Logged out from all device !!'], 200);

        $userToken = $user->token();
        $userToken->delete();
        return response(['message' => 'Logged Successful !!'], 200);
    }
}
