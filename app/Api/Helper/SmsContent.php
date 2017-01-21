<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

use App\AppUserVerifyCode;
use Illuminate\Support\Facades\Log;

/**
 * 推送短信文案编辑处
 *
 * Class GetDoctor
 * @package App\Api\Helper
 */
class SmsContent
{
    /**
     * 发送短信给新的患者注册
     *
     * @param $user
     * @param $doctor
     * @param $phone
     */
    public static function sendSMS_newPatient($user, $doctor, $phone)
    {
        $sms = new Sms();
        //文案：
        $txt = '【医者脉连】' .
            $user->name . '医生刚刚通过“医者脉连”平台为您预约' .
            $doctor->hospital .
            $doctor->dept .
            $doctor->name . '医师的面诊，约诊费约为' .
            (($doctor->fee) / 100) . '元，请在12小时内安装“医者脉连-看专家”客户端进行确认。下载地址：http://pre.im/PHMF 。请确保使用本手机号码进行注册和登陆以便查看该笔预约。';
        $result = $sms->sendSMS($phone, $txt);
        $result = $sms->execResult($result);

        /**
         * 如果出错，则记录信息
         */
        if ($result[1] != 0) {
            Log::info('send-sms-patient', ['context' => json_encode($result)]);
        }
    }

    /**
     * 发送短信给新的注册用户
     *
     * @param $phone
     * @return \Illuminate\Http\JsonResponse
     */
    public static function sendSMS_newUser($phone)
    {
        $newCode = [
            'phone' => $phone,
            'code' => rand(1001, 9998)
        ];

        /**
         * 发送短信:
         */
        $sms = new Sms();
        $txt = '【医者脉连】您的验证码是:' . $newCode['code']; //文案
        $result = $sms->sendSMS($newCode['phone'], $txt);
        $result = $sms->execResult($result);

        if ($result[1] == 0) {
            $code = AppUserVerifyCode::where('phone', '=', $phone)->get();
            if (empty($code->all())) {
                AppUserVerifyCode::create($newCode);
            } else {
                AppUserVerifyCode::where('phone', $phone)->update(['code' => $newCode['code']]);
            }

            return response()->json(['debug' => $newCode['code']], 200);
        } else {
            Log::info('send-sms-new-user', ['context' => json_encode($result)]);
            return response()->json(['message' => '短信发送失败'], 500);
        }
    }
}
