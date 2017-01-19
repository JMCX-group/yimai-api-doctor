<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

use App\Appointment;
use App\AppointmentMsg;
use App\Doctor;
use App\Patient;
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
     * 批量生成和推送
     *
     * @param $appointments
     * @param $status
     */
    public static function sendAppointmentsMsg_list($appointments, $status)
    {
        if (count($appointments) == 0) {
            return;
        }

        /**
         * 生成数据：
         */
        $appointmentIdList = array();
        $appointmentMsgList = array();
        $deviceTokens = array();
        foreach ($appointments as $appointment) {
            /**
             * 符合条件的约诊号
             */
            array_push($appointmentIdList, $appointment->id);

            /**
             * 生成推送消息
             */
            array_push($appointmentMsgList, self::generateAppointmentsMsg($appointment, $status, true));

            /**
             * 符合条件的患者device_token
             */
            $patient = Patient::where('phone', $appointment->patient_phone)->first();
            if (isset($patient->id) && ($patient->device_token != '' && $patient->device_token != null)) {
                array_push($deviceTokens, ['device_token' => $patient->device_token, 'id' => $appointment->id]);
            }
        }

        /**
         * 推送消息
         */
        try {
            $result = Appointment::whereIn('id', $appointmentIdList)
                ->update(['status' => $status]);

            if ($result) {
                AppointmentMsg::insert($appointmentMsgList); //批量插入推送消息

                foreach ($deviceTokens as $deviceToken) {
                    self::pushAppointmentMsg($deviceToken['device_token'], $status, $deviceToken['id'], 'patient'); //向患者端推送消息
                }
            } else {
                Log::info('kernel-updateExpiredAndPushAppointment-update', ['context' => json_encode($appointmentIdList)]);
            }
        } catch (\Exception $e) {
            Log::info('kernel-updateExpiredAndPushAppointment', ['context' => $e->getMessage()]);
        }
    }

    /**
     * 生成约诊推送信息内容
     *
     * @param $appointments
     * @param string $status
     * @param bool $time
     * @return array
     */
    public static function generateAppointmentsMsg($appointments, $status = '', $time = false)
    {
        $status = ($status == '') ? $appointments->status : $status; //有则表示是新状态的，没有则表示通知当前状态
        $type = (in_array($status, array('completed-1', 'completed-2'))) ? 0 : 1; //0为普通，1为重要

        return [
            'appointment_id' => $appointments->id,
            'status' => $status,
            'locums_id' => $appointments->locums_id, //代理医生ID
            'locums_name' => ($appointments->locums_id == 0) ? '无' : Doctor::find($appointments->locums_id)->first()->name, //代理医生姓名
            'patient_name' => $appointments->patient_name,
            'doctor_id' => $appointments->doctor_id,
            'doctor_name' => ($appointments->doctor_id == '') ? '无' : Doctor::find($appointments->doctor_id)->first()->name, //医生姓名
            'type' => $type,
            'created_at' => $time ? '' : date('Y-m-d H:i:s'),
            'updated_at' => $time ? '' : date('Y-m-d H:i:s')
        ];
    }

    /**
     * 推送约诊信息
     *
     * @param $appointments
     * @param string $status
     */
    public static function sendAppointmentsMsg($appointments, $status = '')
    {
        AppointmentMsg::create(self::generateAppointmentsMsg($appointments, $status));
    }

    /**
     * 给指定用户推送约诊信息
     *
     * @param $deviceToken
     * @param $appointmentStatus
     * @param $appointmentId
     * @param $recipient
     */
    public static function pushAppointmentMsg($deviceToken, $appointmentStatus, $appointmentId, $recipient)
    {
        require_once('UmengNotification/NotificationPush.php');

        /**
         * 获取推送文案和动作
         */
        $content = AppointmentStatus::pushContent($appointmentStatus);
        $action = 'appointment';

        if ($recipient == 'doctor') {
            /**
             * 判断是IOS还是Android：
             * Android的device_token是44位字符串, iOS的device_token是64位。
             */
            if (strlen($deviceToken) > 44) {
                /**
                 * IOS推送
                 */
                //医生端企业版
                $pushE = new \NotificationPush('58073c2ae0f55a4ac00023e4', 'npypnjmmor5ufydocxyia3o6lwq1vh5n');
                $pushE_falseResult = $pushE->sendIOSUnicast($deviceToken, $content, $action, $appointmentId);
                $pushE_trueResult = $pushE->sendIOSUnicast($deviceToken, $content, $action, $appointmentId, 'true');
                //医生端AppStore
                $pushApp = new \NotificationPush('587704278f4a9d795e001f79', 'ajcvonw3kas06oyljq1xcujvuadqszcj');
                $pushApp_falseResult = $pushApp->sendIOSUnicast($deviceToken, $content, $action, $appointmentId);
                $pushApp_trueResult = $pushApp->sendIOSUnicast($deviceToken, $content, $action, $appointmentId, 'true');

                self::pushBroadcastIosLog($action, $recipient, $pushE_falseResult, $pushE_trueResult, $pushApp_falseResult, $pushApp_trueResult, $deviceToken);
            } else {
                /**
                 * 安卓推送
                 */
                $push = new \NotificationPush('58073313e0f55a4825002a47', '0hmugthtu84nyou6egw3kmdsf6v4zmom');
                $pushResult = $push->sendAndroidUnicast($deviceToken, $content, $action, $appointmentId);

                self::pushBroadcastAndroidLog($action, $recipient, $pushResult, $deviceToken);
            }
        } else {
            /**
             * 判断是IOS还是Android：
             * Android的device_token是44位字符串, iOS的device_token是64位。
             */
            if (strlen($deviceToken) > 44) {
                /**
                 * IOS推送
                 */
                //患者端企业版
                $pushE = new \NotificationPush('58770533c62dca6297001b7b', 'mnbtm9nu5v2cw5neqbxo6grqsuhxg1o8');
                $pushE_falseResult = $pushE->sendIOSUnicast($deviceToken, $content, $action, $appointmentId);
                $pushE_trueResult = $pushE->sendIOSUnicast($deviceToken, $content, $action, $appointmentId, 'true');
                //患者端AppStore
                $pushApp = new \NotificationPush('587704b3310c934edb002251', 'mngbtbi7lj0y8shlmdvvqdkek9k3hfin');
                $pushApp_falseResult = $pushApp->sendIOSUnicast($deviceToken, $content, $action, $appointmentId);
                $pushApp_trueResult = $pushApp->sendIOSUnicast($deviceToken, $content, $action, $appointmentId, 'true');

                self::pushBroadcastIosLog($action, $recipient, $pushE_falseResult, $pushE_trueResult, $pushApp_falseResult, $pushApp_trueResult, $deviceToken);
            } else {
                /**
                 * 安卓推送
                 */
                $push = new \NotificationPush('587b786af43e4833800004cb', 'oth53caymcr5zxc2edhi0ghuoyuxbov3');
                $pushResult = $push->sendAndroidUnicast($deviceToken, $content, $action, $appointmentId);

                self::pushBroadcastAndroidLog($action, $recipient, $pushResult, $deviceToken);
            }
        }
    }

    /**
     * 推送广播
     *
     * @param $recipient
     * @param $content
     * @param $action
     * @param $dataId
     */
    public static function pushBroadcast($recipient, $content, $action, $dataId)
    {
        require_once('UmengNotification/NotificationPush.php');

        /**
         * IOS推送
         */
        if ($recipient == 'd' || $recipient == 'all') {
            //医生端企业版
            $pushDE = new \NotificationPush('58073c2ae0f55a4ac00023e4', 'npypnjmmor5ufydocxyia3o6lwq1vh5n');
            $pushDE_falseResult = $pushDE->sendIOSBroadcast($content, $action, $dataId);
            $pushDE_trueResult = $pushDE->sendIOSBroadcast($content, $action, $dataId, 'true');
            //医生端AppStore
            $pushDApp = new \NotificationPush('587704278f4a9d795e001f79', 'ajcvonw3kas06oyljq1xcujvuadqszcj');
            $pushDApp_falseResult = $pushDApp->sendIOSBroadcast($content, $action, $dataId);
            $pushDApp_trueResult = $pushDApp->sendIOSBroadcast($content, $action, $dataId, 'true');

            self::pushBroadcastIosLog($action, 'doctor', $pushDE_falseResult, $pushDE_trueResult, $pushDApp_falseResult, $pushDApp_trueResult);
        }

        if ($recipient == 'p' || $recipient == 'all') {
            //患者端企业版
            $pushPE = new \NotificationPush('58770533c62dca6297001b7b', 'mnbtm9nu5v2cw5neqbxo6grqsuhxg1o8');
            $pushPE_falseResult = $pushPE->sendIOSBroadcast($content, $action, $dataId);
            $pushPE_trueResult = $pushPE->sendIOSBroadcast($content, $action, $dataId, 'true');
            //患者端AppStore
            $pushPApp = new \NotificationPush('587704b3310c934edb002251', 'mngbtbi7lj0y8shlmdvvqdkek9k3hfin');
            $pushPApp_falseResult = $pushPApp->sendIOSBroadcast($content, $action, $dataId);
            $pushPApp_trueResult = $pushPApp->sendIOSBroadcast($content, $action, $dataId, 'true');

            self::pushBroadcastIosLog($action, 'patient', $pushPE_falseResult, $pushPE_trueResult, $pushPApp_falseResult, $pushPApp_trueResult);
        }

        /**
         * Android推送
         */
        if ($recipient == 'd' || $recipient == 'all') { //医生端
            $pushD = new \NotificationPush('58073313e0f55a4825002a47', '0hmugthtu84nyou6egw3kmdsf6v4zmom');
            $pushD_result = $pushD->sendAndroidBroadcast($content, 'radio', $dataId);
            self::pushBroadcastAndroidLog($action, 'doctor', $pushD_result);
        }

        if ($recipient == 'p' || $recipient == 'all') { //患者端
            $pushP = new \NotificationPush('587b786af43e4833800004cb', 'oth53caymcr5zxc2edhi0ghuoyuxbov3');
            $pushP_result = $pushP->sendAndroidBroadcast($content, 'radio', $dataId);
            self::pushBroadcastAndroidLog($action, 'patient', $pushP_result);
        }
    }

    /**
     * IOS日志
     *
     * @param $action
     * @param $recipient
     * @param $EF
     * @param $ET
     * @param $AF
     * @param $AT
     * @param $deviceToken
     */
    public static function pushBroadcastIosLog($action, $recipient, $EF, $ET, $AF, $AT, $deviceToken = '')
    {
        if ($EF['result'] == false) {
            Log::info('IOS-push-' . $recipient . '-' . $action . '-E-false', ['context' => $EF['message'], 'device_token' => $deviceToken]);
        }
        if ($ET['result'] == false) {
            Log::info('IOS-push-' . $recipient . '-' . $action . '-E-true', ['context' => $ET['message'], 'device_token' => $deviceToken]);
        }
        if ($AF['result'] == false) {
            Log::info('IOS-push-' . $recipient . '-' . $action . '-App-false', ['context' => $AF['message'], 'device_token' => $deviceToken]);
        }
        if ($AT['result'] == false) {
            Log::info('IOS-push-' . $recipient . '-' . $action . '-App-true', ['context' => $AT['message'], 'device_token' => $deviceToken]);
        }
    }

    /**
     * 安卓日志
     *
     * @param $action
     * @param $recipient
     * @param $result
     * @param $deviceToken
     */
    public static function pushBroadcastAndroidLog($action, $recipient, $result, $deviceToken = '')
    {
        if ($result['result'] == false) {
            Log::info('Android-push-' . $recipient . '-' . $action, ['context' => $result['message'], 'device_token' => $deviceToken]);
        }
    }
}
