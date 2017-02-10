<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\Api\Helper\AppointmentStatus;

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
            'text' => AppointmentStatus::admissionsMsgContent($admissionsMsg['status'], $admissionsMsg['locums_name'], $admissionsMsg['patient_name'], $admissionsMsg['appointment_id']),
            'type' => $admissionsMsg['type'],
            'read' => $admissionsMsg['doctor_read'],
            'time' => $admissionsMsg['created_at']->format('Y-m-d H:i:s'),
            'status' => $admissionsMsg['status']
        ];
    }
}
