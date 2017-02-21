<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

use App\Appointment;
use App\Doctor;

/**
 * 约诊状态翻译
 *
 * Class GetDoctor
 * @package App\Api\Helper
 */
class AppointmentStatus
{
    /**
     * 约诊消息推送文案
     * 给代约/约诊医生看的
     *
     * @param $status
     * @param $doctor
     * @param $patient
     * @param $id
     * @return string
     */
    public static function appointmentMsgContent($status, $doctor, $patient, $id)
    {
        /**
         * Wait:
         * wait-0: 待代约医生确认
         * wait-1: 待患者付款
         * wait-2: 患者已付款，待医生确认
         * wait-3: 医生确认接诊，待面诊
         * wait-4: 医生改期，待患者确认
         * wait-5: 患者确认改期，待面诊
         *
         * Close:
         * close-0: 代约医生拒绝代约
         * close-1: 待患者付款
         * close-2: 医生过期未接诊,约诊关闭
         * close-3: 医生拒绝接诊
         * close-4: 患者过期未确认,约诊关闭
         * close-5: 医生转诊,约诊关闭
         *
         * Cancel:
         * cancel-1: 患者取消约诊; 未付款
         * cancel-2: 医生取消约诊
         * cancel-3: 患者取消约诊; 已付款后
         * cancel-4: 医生改期之后,医生取消约诊;
         * cancel-5: 医生改期之后,患者取消约诊;
         * cancel-6: 医生改期之后,患者确认之后,患者取消约诊;
         * cancel-7: 医生改期之后,患者确认之后,医生取消约诊;
         *
         * Completed:
         * completed-1:最简正常流程
         * completed-2:改期后完成
         */

        switch ($status) {
            case 'wait-0':
                $retText = '患者' . $patient . '请求您代约。';
                break;

            case 'wait-1':
                $retText = '您替' . $patient . '约诊' . $doctor . '医生的信息已发送，等待确认及支付。若12小时内未完成支付则约诊失效。';
                break;

            case 'close-0':
                $retText = '您拒绝了患者' . $patient . '请您代约的约诊（预约号' . $id . '），约诊关闭。';
                break;

            case 'close-1':
                $retText = '患者' . $patient . '逾期未确认您代约' . $doctor . '的约诊（预约号' . $id . '），约诊过期。';
                break;

            case 'close-2':
                $retText = '医生' . $doctor . '过期未接诊，约诊关闭。（预约号' . $id . '）';
                break;

            case 'close-3':
                $retText = $doctor . '医生拒绝了您替患者' . $patient . '发起的约诊请求（预约号' . $id . '），原因为' . Appointment::find($id)->refusal_reason . '，约诊关闭。';
                break;

            case 'close-4':
                $retText = '患者' . $patient . '逾期未确认' . $doctor . '医生的改期，约诊关闭。（预约号' . $id . '）';
                break;

            case 'close-5':
                $retText = $doctor . '医生转诊，约诊关闭。（预约号' . $id . '）';
                break;

            case 'cancel-2':
            case 'cancel-4':
            case 'cancel-7':
                $retText = '医生' . $doctor . '取消了约诊请求。（预约号' . $id . '）';
                break;

            case 'cancel-1':
            case 'cancel-3':
            case 'cancel-5':
            case 'cancel-6':
                $retText = '患者' . $patient . '取消了约诊请求。（预约号' . $id . '）';
                break;

            case 'completed-1':
            case 'completed-2':
                $retText = '您替患者' . $patient . '向' . $doctor . '医生发起的预约（预约号' . $id . '）已完成面诊。';
                break;

            default:
                $retText = false;
                break;
        }

        return $retText;
    }

    /**
     * 接诊消息推送文案
     * 给接诊医生看的
     *
     * @param $status
     * @param $locums
     * @param $patient
     * @param $id
     * @return bool|string
     */
    public static function admissionsMsgContent($status, $locums, $patient, $id)
    {
        /**
         * Wait:
         * wait-0: 待代约医生确认
         * wait-1: 待患者付款
         * wait-2: 患者已付款，待医生确认
         * wait-3: 医生确认接诊，待面诊
         * wait-4: 医生改期，待患者确认
         * wait-5: 患者确认改期，待面诊
         *
         * Close:
         * close-0: 代约医生拒绝代约
         * close-1: 待患者付款
         * close-2: 医生过期未接诊,约诊关闭
         * close-3: 医生拒绝接诊
         * close-4: 患者过期未确认,约诊关闭
         * close-5: 医生转诊,约诊关闭
         *
         * Cancel:
         * cancel-1: 患者取消约诊; 未付款
         * cancel-2: 医生取消约诊
         * cancel-3: 患者取消约诊; 已付款后
         * cancel-4: 医生改期之后,医生取消约诊;
         * cancel-5: 医生改期之后,患者取消约诊;
         * cancel-6: 医生改期之后,患者确认之后,患者取消约诊;
         * cancel-7: 医生改期之后,患者确认之后,医生取消约诊;
         *
         * Completed:
         * completed-1:最简正常流程
         * completed-2:改期后完成
         */
        switch ($status) {
            case 'wait-2':
                $retText = '您收到一条' . $locums . '替患者' . $patient . '发起的约诊请求（预约号' . $id . '），请在48小时内处理。';
                break;
            case 'wait-5':
                $retText = '患者' . $patient . '已确认您将原定的约诊时间改为：' . Appointment::find($id)->rescheduled_time . '。（预约号' . $id . '）';
                break;
            case 'cancel-3':
            case 'cancel-5':
            case 'cancel-6':
                $retText = '患者' . $patient . '取消了约诊请求。';
                break;
            default:
                $retText = false;
                break;
        }

        return $retText;
    }

    /**
     * 约诊消息推送文案
     * 给患者看的
     *
     * @param $status
     * @param $doctorId
     * @param $doctor
     * @param $locums
     * @param $id
     * @return bool|string
     */
    public static function appointmentMsgContent_patient($status, $doctorId, $doctor, $locums, $id)
    {
        /**
         * Wait:
         * wait-0: 待代约医生确认
         * wait-1: 待患者付款
         * wait-2: 患者已付款，待医生确认
         * wait-3: 医生确认接诊，待面诊
         * wait-4: 医生改期，待患者确认
         * wait-5: 患者确认改期，待面诊
         *
         * Close:
         * close-0: 代约医生拒绝代约
         * close-1: 待患者付款
         * close-2: 医生过期未接诊,约诊关闭
         * close-3: 医生拒绝接诊
         * close-4: 患者过期未确认,约诊关闭
         * close-5: 医生转诊,约诊关闭
         *
         * Cancel:
         * cancel-1: 患者取消约诊; 未付款
         * cancel-2: 医生取消约诊
         * cancel-3: 患者取消约诊; 已付款后
         * cancel-4: 医生改期之后,医生取消约诊;
         * cancel-5: 医生改期之后,患者取消约诊;
         * cancel-6: 医生改期之后,患者确认之后,患者取消约诊;
         * cancel-7: 医生改期之后,患者确认之后,医生取消约诊;
         *
         * Completed:
         * completed-1:最简正常流程
         * completed-2:改期后完成
         */
        switch ($status) {
            case 'wait-1':
                $doctorInfo = Doctor::findDoctor($doctorId);
                if ($locums == '无') {
                    $retText = '您向' . $doctor . '(' .
                        $doctorInfo['hospital'] . $doctorInfo['dept'] . $doctorInfo['title'] .
                        ')发起了约诊（预约号' . $id . '），请在12小时内缴费确认。';
                } else {
                    $retText = $locums . '医生替您向' . $doctor . '(' .
                        $doctorInfo['hospital'] . $doctorInfo['dept'] . $doctorInfo['title'] .
                        ')发起了约诊（预约号' . $id . '），请在12小时内缴费确认。';
                }
                break;
            case 'wait-3':
                $retText = $doctor . '医生已确认接诊（预约号' . $id . '），请按时到医院就诊。';
                break;
            case 'wait-4':
                $retText = $doctor . '医生已将接诊（预约号' . $id . '）改期，请及时确认。';
                break;

            case 'close-2':
                if ($doctor == '无') {
                    $retText = $locums . '医生过期未代约（预约号' . $id . '），接诊关闭。';
                } else {
                    $retText = $doctor . '医生过期未接诊（预约号' . $id . '），接诊关闭。';
                }
                break;
            case 'close-0':
                $retText = $locums . '医生拒绝代约（预约号' . $id . '），接诊关闭。';
                break;
            case 'close-3':
                $retText = $doctor . '医生拒绝接诊（预约号' . $id . '），接诊关闭。';
                break;
            case 'close-4':
                $retText = '您逾期未确认' . $doctor . '医生的改期，约诊关闭。（预约号' . $id . '）';
                break;
            case 'close-5':
                $retText = $doctor . '转诊，约诊关闭。（预约号' . $id . '）';
                break;

            case 'cancel-2':
            case 'cancel-4':
            case 'cancel-7':
                $retText = $doctor . '医生取消了约诊请求。';
                break;

            default:
                $retText = false;
                break;
        }

        return $retText;
    }

    /**
     * 约诊状态文案
     *
     * @param $status
     * @return string
     */
    public static function content($status)
    {

        switch ($status) {
            case 'wait-0':
                $retData = '待代约医生确认';
                break;
            case 'wait-1':
                $retData = '待患者付款';
                break;
            case 'wait-2':
                $retData = '患者已付款，待医生确认';
                break;
            case 'wait-3':
                $retData = '医生确认接诊，待面诊';
                break;
            case 'wait-4':
                $retData = '医生改期，待患者确认';
                break;
            case 'wait-5':
                $retData = '患者确认改期，待面诊';
                break;

            case 'close-0':
                $retData = '代约医生拒绝代约，约诊关闭';
                break;
            case 'close-1':
                $retData = '待患者付款';
                break;
            case 'close-2':
                $retData = '医生过期未接诊，约诊关闭';
                break;
            case 'close-3':
                $retData = '医生拒绝接诊';
                break;
            case 'close-4':
                $retData = '患者逾期未确认改期，约诊关闭';
                break;
            case 'close-5':
                $retData = '医生转诊，约诊关闭';
                break;

            case 'cancel-1':
                $retData = '患者取消约诊; 未付款';
                break;
            case 'cancel-2':
                $retData = '医生取消约诊';
                break;
            case 'cancel-3':
                $retData = '患者取消约诊; 已付款后';
                break;
            case 'cancel-4':
                $retData = '医生改期之后,医生取消约诊';
                break;
            case 'cancel-5':
                $retData = '医生改期之后,患者取消约诊';
                break;
            case 'cancel-6':
                $retData = '医生改期之后,患者确认之后,患者取消约诊';
                break;
            case 'cancel-7':
                $retData = '医生改期之后,患者确认之后,医生取消约诊';
                break;

            case 'completed-1':
                $retData = '已完成';
                break;
            case 'completed-2':
                $retData = '改期后完成';
                break;

            default:
                $retData = '';
                break;
        }

        return $retData;
    }

    /**
     * 约诊推送文案
     *
     * @param $status
     * @param string $recipient
     * @param null $appointment
     * @return string
     */
    public static function pushContent($status, $recipient = 'patient', $appointment = null)
    {
        switch ($status) {
            case 'wait-0':
                $retData = '有患者请求您代约';
                break;
            case 'wait-1':
                $retData = '您有新的约诊订单需要支付';
                break;
            case 'wait-2':
                if ($appointment != null && $appointment['is_transfer'] == 1) {
                    $retData = '医生已转诊，请您确认';
                } else {
                    $retData = '患者已付款，待您确认';
                }
                break;
            case 'wait-3':
                $retData = '医生确认接诊，待面诊';
                break;
            case 'wait-4':
                $retData = '医生改期，待您确认';
                break;
            case 'wait-5':
                $retData = '患者确认改期，待面诊';
                break;

            case 'close-0':
                $retData = '代约医生拒绝代约,约诊关闭';
                break;
            case 'close-1':
                $retData = '您逾期未付款,约诊关闭';
                break;
            case 'close-2':
                $retData = '医生过期未接诊,约诊关闭';
                break;
            case 'close-3':
                $retData = '医生拒绝接诊';
                break;
            case 'close-4':
                $retData = (($recipient == 'patient') ? '您' : '患者') . '逾期未确认改期，约诊关闭';
                break;
            case 'close-5':
                $retData = '医生转诊，约诊关闭';
                break;

            case 'cancel-1':
            case 'cancel-3':
            case 'cancel-5':
            case 'cancel-6':
                $retData = '患者取消约诊';
                break;
            case 'cancel-2':
            case 'cancel-4':
            case 'cancel-7':
                $retData = '医生取消约诊';
                break;

            case 'completed-1':
            case 'completed-2':
                $retData = '面诊已完成';
                break;

            default:
                $retData = '';
                break;
        }

        return $retData;
    }
}
