<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if(!Auth::check()) {
            if(!$user = User::where('audius_id', $request->audius_id)->first()) {
                $user = User::create([
                    'audius_id' => $request->audius_id,
                    'audius_handle' => $request->audius_handle,
                    'email' => $request->email,
                    'avatar_url' => $request->avatar_url
                ]);
                Auth::login($user, true);
                return response()->json([
                    'user' => $user
                ]);
            }
            Auth::login($user, true);
        }
        return response()->json([
            'user' => Auth::user()
        ]);
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        return redirect('/');
    }
}
