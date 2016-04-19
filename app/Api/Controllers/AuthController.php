<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午5:45
 */

namespace App\Api\Controllers;


use App\User;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

/**
 * Class AuthController
 * @package App\Api\Controllers
 */
class AuthController extends BaseController
{
    /**
     * User auth.
     * @param Request $request
     * @return mixed
     */
    public function authenticate(Request $request)
    {
        // grab credentials from the request
//        $credentials = $request->only('email', 'password');

        /*
         * 用于自定义用户名和密码字段:
         */
        $credentials = [
            'phone' => $request->get('phone'),
            'password' => $request->get('password')
        ];

        /* 如果password字段在数据库中自定义为pwd,则需要在User.php中增加下列函数:
        public function getAuthPassword()
        {
            return $this->pwd;
        }
        */

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }

    /**
     * User register.
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'phone' => 'required|digits:11|unique:app_users',
                'password' => 'required|min:6|max:60'
            ]);
        } catch (HttpResponseException $e) {
            return response()->json(['error' => 'invalid_info'], 403);
        }

        $newUser = [
            'phone' => $request->get('phone'),
            'password' => bcrypt($request->get('password'))
        ];
        $user = User::create($newUser);
        $token = JWTAuth::fromUser($user);

        return response()->json(compact('token'));
    }

    /**
     * Get logged user info.
     * @return mixed
     */
    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }
}
