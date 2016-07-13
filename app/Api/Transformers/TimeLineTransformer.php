<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\Hospital;
use App\PayRecord;

class TimeLineTransformer
{
    /**
     * 生成时间轴及其文案。
     *
     * @param $appointments
     * @param $doctors
     * @param $myId
     * @param null $locumsDoctor
     * @return array|mixed
     */
    public static function generateTimeLine($appointments, $doctors, $myId, $locumsDoctor = null)
    {
        $retData = array();

        /**
         * 发起约诊的第一个时间点内容:
         */
        $retData = self::otherInfoContent_firstInfo($appointments, $doctors, $retData);

        /**
         * 如果是患者发起代约,多一条信息:
         */
        if ($appointments->doctor_or_patient == 'p') {
            $time = $appointments->confirm_locums_time;
            $infoText = self::confirmLocumsText($appointments, $myId, $locumsDoctor);
            $retData = self::copyTransformer($retData, $time, $infoText, null, 'pass');
        }

        switch ($appointments->status) {
            /**
             * wait:
             * wait-1: 待患者付款
             * wait-2: 患者已付款，待医生确认
             * wait-3: 医生确认接诊，待面诊
             * wait-4: 医生改期，待患者确认
             * wait-5: 患者确认改期，待面诊
             */
            case 'wait-1':
                $infoText = \Config::get('constants.WAIT_PAYMENT');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            case 'wait-2':
                $infoText = \Config::get('constants.ALREADY_PAID_WAIT_CONFIRM');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            case 'wait-3':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);

                $infoText = \Config::get('constants.CONFIRM_ADMISSIONS_WAIT_FACE_CONSULTATION');
                $infoOther = self::infoOther_faceConsultation($appointments, $doctors);
                $retData = self::copyTransformer($retData, null, $infoText, $infoOther, 'notepad');
                break;

            case 'wait-4':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);

                $infoText = \Config::get('constants.DOCTOR_RESCHEDULED_WAIT_CONFIRM');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            case 'wait-5':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = self::otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = self::otherInfoContent_confirmRescheduled($appointments, $retData);

                $infoText = \Config::get('constants.WAIT_FACE_CONSULTATION');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            /**
             * close:
             * close-1: 待患者付款
             * close-2: 医生过期未接诊,约诊关闭
             * close-3: 医生拒绝接诊
             */
            case 'close-1':
                $infoText = \Config::get('constants.NOT_PAY_CLOSE');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'close');
                break;

            case 'close-2':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);

                $infoText = \Config::get('constants.DOCTOR_EXPIRED_APPOINTMENT_CLOSE');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'close');
                break;

            case 'close-3':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);

                $infoText = \Config::get('constants.DOCTOR_APPOINTMENT_CLOSE');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'close');
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
                $infoText = \Config::get('constants.PATIENT_CANCEL_APPOINTMENT');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-2':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = self::otherInfoContent_doctorCancelAdmissions($retData);
                break;

            case 'cancel-3':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);

                $infoText = \Config::get('constants.PATIENT_CANCEL_APPOINTMENT');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-4':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = self::otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = self::otherInfoContent_doctorCancelAdmissions($retData);
                break;

            case 'cancel-5':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = self::otherInfoContent_doctorRescheduled($appointments, $retData);

                $infoText = \Config::get('constants.PATIENT_CANCEL_APPOINTMENT');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-6':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = self::otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = self::otherInfoContent_confirmRescheduled($appointments, $retData);

                $infoText = \Config::get('constants.PATIENT_CANCEL_APPOINTMENT');
                $retData = self::copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-7':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = self::otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = self::otherInfoContent_confirmRescheduled($appointments, $retData);
                $retData = self::otherInfoContent_doctorCancelAdmissions($retData);
                break;

            /**
             * completed:
             * completed-1:最简正常流程
             * completed-2:改期后完成
             */
            case 'completed-1':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);

                $time = $appointments->completed_admissions_time;
                $infoText = \Config::get('constants.CONFIRM_ADMISSIONS');
                $retData = self::copyTransformer($retData, $time, $infoText, null, 'pass');

                $retData = self::otherInfoContent_completed($appointments, $retData);
                break;

            case 'completed-2':
                $retData = self::otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = self::otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = self::otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = self::otherInfoContent_confirmRescheduled($appointments, $retData);
                $retData = self::otherInfoContent_completed($appointments, $retData);
                break;

            default:
                $retData = [];
                break;
        }

        return $retData;
    }

    /**
     * 发起约诊的第一个时间点内容。
     *
     * @param $appointments
     * @param $doctors
     * @param $retData
     * @return mixed
     */
    private static function otherInfoContent_firstInfo($appointments, $doctors, $retData)
    {
        $time = $appointments->created_at->format('Y-m-d H:i:s');
        $infoText = self::beginText($appointments, $doctors);
        $infoOther = [[
            'name' => \Config::get('constants.DESIRED_TREATMENT_TIME'),
            'content' => PublicTransformer::expectVisitDateTransform($appointments->expect_visit_date, $appointments->expect_am_pm)
        ]];
        return self::copyTransformer($retData, $time, $infoText, $infoOther, 'begin');
    }

    /**
     * 医生取消约诊的文案段。
     *
     * @param $retData
     * @return mixed
     */
    private static function otherInfoContent_doctorCancelAdmissions($retData)
    {
        $infoText = \Config::get('constants.DOCTOR_CANCEL_ADMISSIONS');
        return self::copyTransformer($retData, null, $infoText, null, 'no');
    }

    /**
     * 医生确认改期的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private static function otherInfoContent_confirmRescheduled($appointments, $retData)
    {
        $time = $appointments->confirm_rescheduled_time;
        $infoText = \Config::get('constants.CONFIRM_RESCHEDULED');
        return self::copyTransformer($retData, $time, $infoText, null, 'pass');
    }

    /**
     * 患者已经支付的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private static function otherInfoContent_alreadyPaid($appointments, $retData)
    {
        $payRecord = PayRecord::where('transaction_id', $appointments->transaction_id)->get()->first();
        $time = $payRecord->created_at->format('Y-m-d H:i:s');
        $infoText = \Config::get('constants.ALREADY_PAID');
        return self::copyTransformer($retData, $time, $infoText, null, 'pass');
    }

    /**
     * 医生改期的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private static function otherInfoContent_doctorRescheduled($appointments, $retData)
    {
        $time = $appointments->rescheduled_time;
        $infoText = \Config::get('constants.DOCTOR_RESCHEDULED');
        $infoOther = [[
            'name' => \Config::get('constants.RESCHEDULED_TIME'),
            'content' => $appointments->new_visit_time . ' ' . (($appointments->new_am_pm == 'am') ? '上午' : '下午')
        ]];
        return self::copyTransformer($retData, $time, $infoText, $infoOther, 'time');
    }

    /**
     * 患者缴费后,医生确认约诊,等待面诊的文案段。
     *
     * @param $appointments
     * @param $doctors
     * @param $retData
     * @return mixed
     */
    private static function otherInfoContent_confirmAdmissions($appointments, $doctors, $retData)
    {
        $time = $appointments->confirm_admissions_time;
        $infoText = \Config::get('constants.CONFIRM_ADMISSIONS_WAIT_FACE_CONSULTATION');
        $infoOther = self::infoOther_faceConsultation($appointments, $doctors);
        return self::copyTransformer($retData, $time, $infoText, $infoOther, 'notepad');
    }

    /**
     * 完成约诊的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private static function otherInfoContent_completed($appointments, $retData)
    {
        $time = $appointments->updated_at->format('Y-m-d H:i:s');
        $infoText = \Config::get('constants.FACE_CONSULTATION_COMPLETE');
        return self::copyTransformer($retData, $time, $infoText, null, 'completed');
    }

    /**
     * 面诊的附加信息段
     *
     * @param $appointments
     * @param $doctors
     * @return array
     */
    private static function infoOther_faceConsultation($appointments, $doctors)
    {
        if ($appointments->supplement == null || $appointments->supplement == '') {
            $supplement = Hospital::where('id', $doctors->hospital_id)->get()->lists('address')->first();
        } else {
            $supplement = $appointments->supplement;
        }

        return [[
            'name' => \Config::get('constants.TREATMENT_TIME'),
            'content' => $appointments->visit_time . ' ' . (($appointments->am_pm == 'am') ? '上午' : '下午')
        ], [
            'name' => \Config::get('constants.TREATMENT_HOSPITAL'),
            'content' => $doctors->hospital
        ], [
            'name' => \Config::get('constants.SUPPLEMENT'),
            'content' => $supplement
        ], [
            'name' => \Config::get('constants.TREATMENT_NOTICE'),
            'content' => $appointments->remark
        ],];
    }

    /**
     * 生成顶部的进度状态字。
     *
     * @param $status
     * @return array
     */
    public static function generateProgressStatus($status)
    {
        switch ($status) {
            /**
             * wait:
             */
            case 'wait-1':
                $retData = ['milestone' => '发起约诊', 'status' => '待付款'];
                break;
            case 'wait-2':
                $retData = ['milestone' => '患者确认', 'status' => '待确认'];
                break;
            case 'wait-3':
            case 'wait-5':
                $retData = ['milestone' => '医生确认', 'status' => '待面诊'];
                break;
            case 'wait-4':
                $retData = ['milestone' => '医生确认', 'status' => '改期待确认'];
                break;

            /**
             * close:
             */
            case 'close-1':
                $retData = ['milestone' => '发起约诊', 'status' => '已关闭'];
                break;
            case 'close-2':
            case 'close-3':
                $retData = ['milestone' => '患者确认', 'status' => '已关闭'];
                break;

            /**
             * cancel:
             */
            case 'cancel-1':
                $retData = ['milestone' => '发起约诊', 'status' => '已取消'];
                break;
            case 'cancel-2':
            case 'cancel-3':
            case 'cancel-4':
            case 'cancel-5':
            case 'cancel-6':
            case 'cancel-7':
                $retData = ['milestone' => '医生确认', 'status' => '已取消'];
                break;

            /**
             * completed:
             */
            case 'completed-1':
            case 'completed-2':
                $retData = ['milestone' => '面诊完成', 'status' => null];
                break;

            default:
                $retData = [];
                break;
        }

        return $retData;
    }

    /**
     * 第一句文案的角色名称替换
     *
     * @param $appointments
     * @param $doctors
     * @return mixed
     */
    public static function beginText($appointments, $doctors)
    {
        if ($appointments->doctor_or_patient == 'd') {
            $text = \Config::get('constants.APPOINTMENT_DEFAULT');
            $text = str_replace('{代约医生}', $appointments->locums_name, $text);
            $text = str_replace('{患者}', $appointments->patient_name, $text);
            $text = str_replace('{医生}', $doctors->name, $text);
        } else {
            $text = \Config::get('constants.APPOINTMENT_DEFAULT_REQUEST');
            $text = str_replace('{患者}', $appointments->patient_name, $text);
        }

        return $text;
    }

    /**
     * 确认代约文案的角色名称替换
     *
     * @param $appointments
     * @param $myId
     * @param null $locumsDoctor
     * @return mixed
     */
    public static function confirmLocumsText($appointments, $myId, $locumsDoctor = null)
    {
        if ($appointments->locums_id == $myId) {
            $text = \Config::get('constants.CONFIRM_APPOINTMENT');
            $text = str_replace('{人称}', '您', $text);
        } else {
            $text = \Config::get('constants.CONFIRM_APPOINTMENT');
            $text = str_replace('{人称}', $locumsDoctor['name'], $text);
        }

        return $text;
    }

    /**
     * 格式化文案
     *
     * @param $retData
     * @param $time
     * @param $infoText
     * @param $infoOther
     * @param $type
     * @return mixed
     */
    public static function copyTransformer($retData, $time, $infoText, $infoOther, $type)
    {
        array_push(
            $retData,
            [
                'time' => $time,
                'info' => [
                    'text' => $infoText,
                    'other' => $infoOther
                ],
                'type' => $type
            ]
        );

        return $retData;
    }
}
