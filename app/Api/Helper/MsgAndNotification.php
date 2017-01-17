<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

use App\AppointmentMsg;
use App\Doctor;
use Illuminate\Support\Facades\Log;

/**
 * 推送消息和通知
 *
 * Class GetDoctor
 * @package App\Api\Helper
 */
class MsgAndNotification
{
    /**
     * 发送约诊信息
     *
     * @param $appointments
     */
    public static function sendAppointmentsMsg($appointments)
    {
        /**
         * 推送消息记录
         */
        $msgData = [
            'appointment_id' => $appointments->id,
            'locums_id' => $appointments->locums_id, //代理医生ID
            'locums_name' => Doctor::find($appointments->locums_id)->first()->name, //代理医生姓名
            'patient_name' => $appointments->patient_name,
            'doctor_id' => $appointments->doctor_id,
            'doctor_name' => Doctor::find($appointments->doctor_id)->first()->name, //医生姓名
            'status' => $appointments->status //根据上面流程赋值
        ];

        AppointmentMsg::create($msgData);
    }

    /**
     * 给患者推送约诊信息
     *
     * @param $deviceToken
     * @param $appointmentId
     * @return array
     */
    public static function pushMsg($deviceToken, $appointmentId)
    {
        require(dirname(__FILE__) . '/UmengNotification/NotificationPush.php');

        /**
         * 判断是IOS还是Android：
         * Android的device_token是44位字符串, iOS的device-token是64位。
         */
        if (strlen($deviceToken) > 44) {
            //患者端企业版
            $push = new \NotificationPush('58770533c62dca6297001b7b', 'mnbtm9nu5v2cw5neqbxo6grqsuhxg1o8');
            //患者端AppStore
//            $push = new \NotificationPush('587704b3310c934edb002251', 'mngbtbi7lj0y8shlmdvvqdkek9k3hfin');
            $pushResult = $push->sendIOSUnicast($deviceToken, '您有新的约诊订单需要支付', 'appointment', $appointmentId);
        } else {
            $push = new \NotificationPush('587b786af43e4833800004cb', 'oth53caymcr5zxc2edhi0ghuoyuxbov3');
            $pushResult = $push->sendAndroidUnicast($deviceToken, '您有新的约诊订单需要支付', 'appointment', $appointmentId);
        }

        /**
         * 如果出错，则记录日志
         */
        if ($pushResult['result'] == false) {
            Log::info('push-appointment-patient', ['context' => $pushResult['message']]);
        }

        return $pushResult;
    }
}
