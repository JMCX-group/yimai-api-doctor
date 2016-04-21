<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午5:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\AuthRequest;
use App\Api\Requests\InviterRequest;
use App\Api\Transformers\UserTransformer;
use App\User;
use App\AppUserVerifyCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     *
     * @param Request $request
     * @return mixed
     */
    public function authenticate(Request $request)
    {
        /*
         * 用于自定义用户名和密码字段:
         */
        $credentials = [
            'phone' => $request->get('phone'),
            'password' => $request->get('password')
        ];

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['message' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }

    /**
     * User register.
     *
     * @param AuthRequest $request
     * @return mixed
     */
    public function register(AuthRequest $request)
    {
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
     *
     * @return mixed
     */
    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent'], $e->getStatusCode());
        }

        // the token is valid and we have found the user via the sub claim
        return $this->response->item($user, new UserTransformer());
    }

    /**
     * Send verify code.
     *
     * @param AuthRequest $request
     * @return mixed
     */
    public function sendVerifyCode(AuthRequest $request)
    {
        $newCode = [
            'phone' => $request->get('phone'),
            'code' => rand(1001, 9998)
        ];
        $code = AppUserVerifyCode::where('phone', '=', $request->get('phone'))->get();
        if (empty($code->all())) {
            AppUserVerifyCode::create($newCode);
        } else {
            DB::table('app_user_verify_codes')
                ->where('phone', $request->get('phone'))
                ->update(['code' => $newCode['code']]);
        }

        return response()->json(['debug' => $newCode['code']], 200);
    }

    /**
     * Get inviter name.
     * 
     * @param InviterRequest $request
     * @return mixed
     */
    public function getInviter(InviterRequest $request)
    {
        $code = User::select('name')->where('dp_code', '=', $request->get('inviter'))->get();

        return response()->json(['name' => $code->first()->name]);
    }
}
