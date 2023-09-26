<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = User::where([
                'provider' => $provider,
                'provider_id' => $socialUser->id,
            ])->first();

            if ($user) {
                // Check if the user is verified
                if ($user->email) {
                    Auth::login($user);
                    return redirect('/dashboard');
                } else {
                    return redirect('/login')->withErrors([
                        'email' => 'Email is not verified. Please verify your email before logging in.'
                    ]);
                }
            } else {
                // Create a new user and redirect to dashboard
                $user = User::create([
                    'name' => $socialUser->name,
                    'username' => User::generateUserName($socialUser->nickname),
                    'email' => $socialUser->email,
                    'provider_id' => $socialUser->id,
                    'provider' => $provider,
                    'provider_token' => $socialUser->token,
                    'email_verified_at' => now(),
                ]);

                Auth::login($user);
                return redirect('/dashboard');
            }
        }  catch (\Exception $e) {
            return redirect('/login');
        }

        // $user = User::updateOrCreate([
        //     'provider_id' => $SocialUser->id,
        //     'provider' => $provider,
        // ], [
        //     'name' => $SocialUser->name,
        //     'username' => User::generateUserName($SocialUser->nickname),
        //     'email' => $SocialUser->email,
        //     'provider_token' => $SocialUser->token,
        // ]);


    }
}
