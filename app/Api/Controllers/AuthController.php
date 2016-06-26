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
use App\Api\Requests\ResetPwdRequest;
use App\Api\Transformers\UserTransformer;
use App\User;
use App\AppUserVerifyCode;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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
            'password' => bcrypt($request->get('password')),

            'admission_set_fixed' => '[{"week":"sun","am":"false","pm":"false"},{"week":"mon","am":"false","pm":"false"},{"week":"tue","am":"false","pm":"false"},{"week":"wed","am":"false","pm":"false"},{"week":"thu","am":"false","pm":"false"},{"week":"fri","am":"false","pm":"false"},{"week":"sat","am":"false","pm":"false"}]'
        ];
        $user = User::create($newUser);
        $token = JWTAuth::fromUser($user);

        return response()->json(compact('token'));
    }

    /**
     * User reset password.
     * 
     * @param ResetPwdRequest $request
     * @return mixed
     */
    public function resetPassword(ResetPwdRequest $request)
    {
        $userId = User::where('phone', $request->get('phone'))
            ->update(['password' => bcrypt($request->get('password'))]);

        if (!$userId) {
            return response()->json(['message' => '该手机号未注册'], 404);
        }

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

//        $user = User::where('phone', $request->get('phone'))->get();
//        $token = JWTAuth::fromUser($user);
//
//        return response()->json(compact('token'));
    }

    /**
     * Get logged user info.
     * 
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function getAuthenticatedUser()
    {
        $user = User::getAuthenticatedUser();
        if(!isset($user->id)){
            return $user;
        }
        
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
            AppUserVerifyCode::where('phone', $request->get('phone'))->update(['code' => $newCode['code']]);
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
        $data = User::getInviter($request->get('inviter'));

        if ($data) {
            return response()->json(['name' => $data]);
        } else {
            return response()->json(['message' => '无法识别邀请人']);
        }
    }
}
