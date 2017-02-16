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
 * Class SmsContent
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
        $txt = '【医者脉连】' .
            $user->name . '医生刚刚通过“医者脉连”平台为您预约' .
            $doctor->hospital .
            $doctor->dept .
            $doctor->name . '医师的面诊，约诊费约为' .
            $doctor->fee . '元，请在12小时内安装“医者脉连-看专家”客户端进行确认。下载地址：http://pre.im/PHMF 。请确保使用本手机号码进行注册和登陆以便查看该笔预约。';

        self::sendSms($phone, $txt, 'send-sms-patient');
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
        $txt = '【医者脉连】您的验证码是:' . $newCode['code']; //文案

        $ret = self::sendSms($newCode['phone'], $txt, 'send-sms-new-user');

        if ($ret) {
            $code = AppUserVerifyCode::where('phone', '=', $phone)->get();
            if (empty($code->all())) {
                AppUserVerifyCode::create($newCode);
            } else {
                AppUserVerifyCode::where('phone', $phone)->update(['code' => $newCode['code']]);
            }

//            return response()->json(['debug' => $newCode['code']], 200);
            return response()->json(['debug' => ''], 200);
        } else {
            return response()->json(['message' => '短信发送失败'], 500);
        }
    }

    /**
     * 发送合作专区邀请短信给新的注册用户
     *
     * @param $phone
     * @param string $name
     * @param string $content
     * @return bool
     */
    public static function sendSMS_zoneInvite($phone, $name='', $content='')
    {
        if($content){
            $txt = $content;
        } else {
            $txt = '【医者脉连】您的朋友' . $name . '邀请您登陆医脉医生端，在医脉平台，您可以建立属于您自己的个人品牌。'; //文案
        }

        return self::sendSms($phone, $txt, 'send-sms-zone-invite');
    }

    /**
     * 发送邀请短信
     *
     * @param $dpCode
     * @param $name
     * @param $phoneArr
     */
    public static function sendSms_invite($dpCode, $name, $phoneArr)
    {
        foreach ($phoneArr as $phone) {
            $txt = '【医者脉连】您的医生朋友' . $name .
                '邀请您共同使用"医者脉连"，互相约诊更方便。医者仁心，脉脉相连。下载地址：http://pre.im/H5P2 。' .
                '下载后输入医脉码' . $dpCode .
                '可直接添加' . $name .
                '为好友。';
            self::sendSms($phone, $txt, 'send-sms-invite');
        }
    }

    /**
     * 发送短信并记录日志
     *
     * @param $phone
     * @param $txt
     * @param $logName
     * @return bool
     */
    public static function sendSms($phone, $txt, $logName)
    {
        $sms = new Sms();
        $result = $sms->sendSMS($phone, $txt);
        $result = $sms->execResult($result);

        if ($result[1] == 0) {
            return true;
        } else {
            Log::info($logName, ['context' => json_encode($result)]);
            return false;
        }
    }
}
