<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use \Laravel\Socialite\Facades\Socialite;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = User::where('email', $socialUser->getEmail())->first();
            if ($user) {
                \Auth::login($user);

                return redirect('/home');
            }

            $newUser = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'social_id' => $provider . '||' .$socialUser->getId(),
                'password' => Hash::make(Str::random(16)),
            ]);

            \Auth::login($newUser);

            return redirect()->back();

        } catch (\Exception $e) {
            \Log::error($e);
            return redirect('auth/'.$provider);
        }
    }
}
