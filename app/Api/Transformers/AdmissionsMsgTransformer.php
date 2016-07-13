<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

class AdmissionsMsgTransformer
{
    /**
     * @param $admissionsMsg
     * @return array
     */
    public static function transformerMsgList($admissionsMsg)
    {
        return [
            'id' => $admissionsMsg['id'],
            'appointment_id' => $admissionsMsg['appointment_id'],
            'text' => self::generateContent($admissionsMsg),
            'type' => $admissionsMsg['type'],
            'read' => $admissionsMsg['read_status'],
            'time' => $admissionsMsg['created_at']->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @param $data
     * @return bool|string
     */
    public static function generateContent($data)
    {
        switch ($data->status) {
            /**
             * wait:
             * wait-1: 待患者付款
             * wait-2: 患者已付款，待医生确认
             * wait-3: 医生确认接诊，待面诊
             * wait-4: 医生改期，待患者确认
             * wait-5: 患者确认改期，待面诊
             */
            case 'wait-1':
                break;

            case 'wait-2':
                $retText = '您收到一条'.$data['locums_name'].'替患者'.$data['patient_name'].'发起的约诊请求（预约号'.$data['appointment_id'].'），请在48小时内处理.';
                break;

            case 'wait-3':
                break;

            case 'wait-4':
                break;

            case 'wait-5':
                break;

            /**
             * close:
             * close-1: 待患者付款
             * close-2: 医生过期未接诊,约诊关闭
             * close-3: 医生拒绝接诊
             */
            case 'close-1':
                break;

            case 'close-2':
                break;

            case 'close-3':
                break;

            /**
             * cancel:
             * cancel-1: 患者取消约诊; 未付款
             * cancel-2: 医生取消约诊
             * cancel-3: 患者取消约诊; 已付款后
             * cancel-4: 医生改期之后,医生取消约诊;
             * cancel-5: 医生改期之后,患者取消约诊;
             * cancel-6: 医生改期之后,患者确认之后,患者取消约诊;
             * cancel-7: 医生改期之后,患者确认之后,医生取消约诊;
             */
            case 'cancel-1':
                break;

            case 'cancel-2':
                break;

            case 'cancel-3':
                break;

            case 'cancel-4':
                break;

            case 'cancel-5':
                break;

            case 'cancel-6':
                break;

            case 'cancel-7':
                break;

            /**
             * completed:
             * completed-1:最简正常流程
             * completed-2:改期后完成
             */
            case 'completed-1':
                break;

            case 'completed-2':
                break;

            default:
                $retText = false;
                break;
        }

        return $retText;
    }
}
