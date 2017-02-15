<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午5:45
 */

namespace App\Api\Controllers;

use App\Api\Helper\SmsContent;
use App\Api\Requests\AuthRequest;
use App\Api\Requests\InviterRequest;
use App\Api\Requests\ResetPhoneRequest;
use App\Api\Requests\ResetPwdRequest;
use App\Api\Transformers\UserTransformer;
use App\InvitedDoctor;
use App\Patient;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

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
        $domain = \Config::get('constants.DOMAIN');
        $newUser = [
            'phone' => $request->get('phone'),
            'password' => bcrypt($request->get('password')),

            'inviter_dp_code' => isset($request['inviter_dp_code']) ? $request->get('inviter_dp_code') : '',

            'avatar' => $domain . '/uploads/avatar/default.jpg',
            'admission_set_fixed' => '[{"week":"sun","am":"false","pm":"false"},{"week":"mon","am":"false","pm":"false"},{"week":"tue","am":"false","pm":"false"},{"week":"wed","am":"false","pm":"false"},{"week":"thu","am":"false","pm":"false"},{"week":"fri","am":"false","pm":"false"},{"week":"sat","am":"false","pm":"false"}]'
        ];
        $user = User::create($newUser);

        /**
         * 判断是否医脉合作专区邀请：
         */
        if (isset($request['inviter_dp_code']) && $request['inviter_dp_code'] != '') {
            $this->newInvitationRecord($request['inviter_dp_code'], $user);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('token'));
    }

    /**
     * 新建邀请记录
     *
     * @param $code
     * @param $user
     */
    public function newInvitationRecord($code, $user)
    {
        $patient = Patient::getInviter($code);

        if (!$patient) {
            $data = [
                'doctor_id' => $user->id,
                'patient_id' => $patient->id
            ];
            InvitedDoctor::create($data);
        }
    }

    /**
     * User reset phone.
     *
     * @param ResetPhoneRequest $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function resetPhone(ResetPhoneRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }
        $newPhone = $request->get('phone');

        /**
         * 判断是否未修改
         */
        if ($user->phone == $newPhone) {
            return response()->json(['message' => '该手机号未有变化'], 404);
        }

        /**
         * 判断是否已经注册
         */
        $getData = User::where('phone', $newPhone)->get();
        if (!$getData->isEmpty()) {
            return response()->json(['message' => '该手机号已注册'], 404);
        }

        /**
         * 修改信息
         */
        $user->phone = $newPhone;
        $user->save();
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
    }

    /**
     * Get logged user info.
     *
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function getAuthenticatedUser()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
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
        return SmsContent::sendSMS_newUser($request->get('phone'));
    }

    /**
     * Get inviter name.
     *
     * @param InviterRequest $request
     * @return mixed
     */
    public function getInviter(InviterRequest $request)
    {
        $inviter = User::getInviter($request->get('inviter'));

        if ($inviter) {
            return response()->json(['name' => $inviter->name]);
        } else {
            return response()->json(['message' => '无法识别邀请人'], 400);
        }
    }
}
