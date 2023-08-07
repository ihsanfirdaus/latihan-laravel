<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $token = md5(uniqid());

        $user->update(['token' => $token]);
    }
    
    public function sso(Request $request)
    {
        $token = $request->get('token');

        if ($token === null) {
            return "Token not found";
        }

        $response = Http::post(env('BASE_URL_SSO') . '/api/auth/verify-jwt-token', [
            'token' => $token
        ]);

        $user = null;

        if ($response->status() === 200)
        {
            $responseBody = json_decode($response->body());
            $user = User::find($responseBody->id);
        }

        if ($user !== null) {
            Auth::login($user);

            return redirect('/');
        }
    }
}
