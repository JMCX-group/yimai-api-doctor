<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use League\Fractal\TransformerAbstract;

class AdmissionsRecordTransformer extends TransformerAbstract
{
    /**
     * @param $appointment
     * @return array
     */
    public static function admissionsTransform($appointment)
    {
        return [
            'id' => $appointment['id'],
            'doctor_id' => $appointment['doctor_id'],
            'doctor_name' => $appointment['name'],
            'doctor_head_url' => ($appointment['avatar'] == '') ? null : $appointment['avatar'],
            'doctor_job_title' => $appointment['title'],
            'doctor_is_auth' => $appointment['auth'],
            'hospital' => $appointment['hospital'],
            'patient_name' => $appointment['patient_name'],
            'patient_head_url' => ($appointment['patient_avatar'] == '') ? null : $appointment['patient_avatar'],
            'patient_gender' => $appointment['patient_gender'],
            'patient_age' => $appointment['patient_age'],
            'time' => PublicTransformer::generateTreatmentTime($appointment),
            'status' => self::generateStatus($appointment['status']),
            'who' => ($appointment['doctor_or_patient'] == 'd') ? '医生代约' : '患者约诊'
        ];
    }

    /**
     * @param $status
     * @return array|string
     */
    public static function generateStatus($status)
    {
        switch ($status) {
            case 'wait-1':
            case 'wait-4':
                $retData = '待确认';
                break;

            case 'wait-2':
                $retData = '待确认';
                break;

            case 'wait-3':
            case 'wait-5':
                $retData = '待面诊';
                break;

            case 'close-1':
            case 'cancel-1':
            case 'cancel-3':
            case 'cancel-5':
            case 'cancel-6':
                $retData = '患者关闭';
                break;

            case 'close-2':
            case 'close-3':
            case 'cancel-2':
            case 'cancel-4':
            case 'cancel-7':
                $retData = '医生关闭';
                break;

            case 'completed-1':
            case 'completed-2':
                $retData = '已完成';
                break;

            default:
                $retData = [];
                break;
        }

        return $retData;
    }
}
