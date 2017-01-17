<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

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
        $ret = $sms->sendSMS($phone, $txt);


        /**
         * 如果出错，则记录信息
         */
//        if ($ret 怎么怎么了) {
//            Log::info('send-sms-patient', ['context' => $pushResult['message']]);
//        }
    }
}
