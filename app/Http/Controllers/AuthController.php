<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        // TODO: remove token expiration is needed
        if(false) {
            $claims = config('jwt.required_claims');
            // remove 'exp' from the required claims
            if (($key = array_search('exp', $claims)) !== false) {
                unset($claims[$key]);
                $claims = array_values($claims);
            }

            app('config')->set('jwt.required_claims', $claims);
            // set ttl to null so the 'exp' field isn't set
            app('config')->set('jwt.ttl', null);
        }

        try {
            // attempt to verify the credentials and create a token for the user
            $token = $this->jwt->attempt($credentials);

            if (!$token) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['error' => 'token_expired'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['error' => 'token_invalid'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['error' => 'token_absent', 'token_absent' => $e->getMessage()], 500);

        }

        return response()->json(compact('token'));
    }
}
