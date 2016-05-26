<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\AppointmentIdRequest;
use App\Api\Requests\AppointmentRequest;
use App\Api\Transformers\Transformer;
use App\Appointment;
use App\Hospital;
use App\PayRecord;
use App\User;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Exceptions\JWTException;

class AppointmentController extends BaseController
{
    public function index()
    {

    }

    /**
     * @param AppointmentRequest $request
     * @return array|mixed
     */
    public function store(AppointmentRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        /**
         * 计算预约码做ID.
         * 规则:01-99 . 年月日各两位长 . 0001-9999
         */
        $frontId = '01' . date('ymd');
        $lastId = Appointment::where('id', 'like', $frontId . '%')
            ->orderBy('id', 'desc')
            ->lists('id');
        if ($lastId->isEmpty()) {
            $nowId = '0001';
        } else {
            $lastId = intval(substr($lastId[0], 8));
            $nowId = str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        }

        $data = [
            'id' => $frontId . $nowId,
            'locums_id' => $user->id, //代理医生ID
            'patient_name' => $request['name'],
            'patient_phone' => $request['phone'],
            'patient_gender' => $request['sex'],
            'patient_age' => $request['age'],
            'patient_history' => $request['history'],
            'doctor_id' => $request['doctor'],
            'expect_visit_time' => $request['time'],
            'expect_am_pm' => $request['am_or_pm'],
        ];

        try {
            Appointment::create($data);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        return ['id' => $frontId . $nowId];
    }

    /**
     * 上传图片
     *
     * @param AppointmentIdRequest $request
     * @return array
     */
    public function uploadImg(AppointmentIdRequest $request)
    {
        $appointment = Appointment::find($request['id']);
        $imgUrl = $this->saveImg($appointment->id, $request->file('img'));

        if (strlen($appointment->patient_imgs) > 0) {
            $appointment->patient_imgs .= ',' . $imgUrl;
        } else {
            $appointment->patient_imgs = $imgUrl;
        }

        $appointment->save();

        return ['url' => $imgUrl];
    }

    /**
     * 保存图片并另存一个压缩图片
     *
     * @param $appointmentId
     * @param $imgFile
     * @return string
     */
    public function saveImg($appointmentId, $imgFile)
    {
        $destinationPath =
            \Config::get('constants.CASE_HISTORY_SAVE_PATH') .
            date('Y') . '/' . date('m') . '/' .
            $appointmentId . '/';
        $filename = time() . '.jpg';

        $imgFile->move($destinationPath, $filename);

        $fullPath = $destinationPath . $filename;
        $newPath = str_replace('.jpg', '_thumb.jpg', $fullPath);

        Image::make($fullPath)->encode('jpg', 30)->save($newPath); //按30的品质压缩图片

        return '/' . $newPath;
    }

    /**
     * @param $id
     * @return array
     */
    public function getDetailInfo($id)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $appointments = Appointment::where('appointments.id', $id)
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.locums_id')
            ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id')
            ->select('appointments.*', 'doctors.name as locums_name', 'patients.avatar as patient_avatar')
            ->get()
            ->first();

        $doctors = User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.title',
            'hospitals.name AS hospital', 'dept_standards.name AS dept')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->where('doctors.id', $appointments->doctor_id)
            ->get()
            ->first();

        /**
         * 自己不是代约医生的话,需要查询代约医生的信息:
         */
        if ($user->id != $appointments->locums_id) {
            $locumsDoctor = User::select(
                'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.title',
                'hospitals.name AS hospital', 'dept_standards.name AS dept')
                ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
                ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
                ->where('doctors.id', $appointments->locums_id)
                ->get()
                ->first();

            $appointments['time_line'] = $this->generateTimeLine($appointments, $doctors, $user->id, $locumsDoctor);
        } else {
            $appointments['time_line'] = $this->generateTimeLine($appointments, $doctors, $user->id);
        }

        $appointments['progress'] = $this->generateProgressStatus($appointments->status);

        return Transformer::appointmentsTransform($appointments, $doctors);
    }

    /**
     * 生成时间轴及其文案。
     *
     * @param $appointments
     * @param $doctors
     * @param $myId
     * @param null $locumsDoctor
     * @return array|mixed
     */
    public function generateTimeLine($appointments, $doctors, $myId, $locumsDoctor = null)
    {
        $retData = array();

        /**
         * 发起约诊的第一个时间点内容:
         */
        $time = $appointments->created_at->format('Y-m-d H:i:s');
        $infoText = $this->beginText($appointments, $doctors);
        $infoOther = [[
            'name' => \Config::get('constants.DESIRED_TREATMENT_TIME'),
            'content' => $appointments->expect_visit_time . ' ' . (($appointments->expect_am_pm == 'am') ? '上午' : '下午')
        ]];
        $retData = $this->copyTransformer($retData, $time, $infoText, $infoOther, 'begin');
        /**
         * 如果是患者发起代约,多一条信息:
         */
        if ($appointments->doctor_or_patient == 'p') {
            $time = $appointments->confirm_locums_time;
            $infoText = $this->confirmLocumsText($appointments, $myId, $locumsDoctor);
            $retData = $this->copyTransformer($retData, $time, $infoText, null, 'pass');
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
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            case 'wait-2':
                $infoText = \Config::get('constants.ALREADY_PAID_WAIT_CONFIRM');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            case 'wait-3':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);

                $infoText = \Config::get('constants.CONFIRM_ADMISSIONS_WAIT_FACE_CONSULTATION');
                $infoOther = $this->infoOther_faceConsultation($appointments, $doctors);
                $retData = $this->copyTransformer($retData, null, $infoText, $infoOther, 'notepad');
                break;

            case 'wait-4':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);

                $infoText = \Config::get('constants.DOCTOR_RESCHEDULED_WAIT_CONFIRM');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            case 'wait-5':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = $this->otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = $this->otherInfoContent_confirmRescheduled($appointments, $retData);

                $infoText = \Config::get('constants.WAIT_FACE_CONSULTATION');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'wait');
                break;

            /**
             * close:
             * close-1: 待患者付款
             * close-2: 医生过期未接诊,约诊关闭
             * close-3: 医生拒绝接诊
             */
            case 'close-1':
                $infoText = \Config::get('constants.NOT_PAY_CLOSE');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'close');
                break;

            case 'close-2':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);

                $infoText = \Config::get('constants.DOCTOR_EXPIRED_APPOINTMENT_CLOSE');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'close');
                break;

            case 'close-3':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);

                $infoText = \Config::get('constants.DOCTOR_APPOINTMENT_CLOSE');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'close');
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
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-2':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = $this->otherInfoContent_doctorCancelAdmissions($retData);
                break;

            case 'cancel-3':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);

                $infoText = \Config::get('constants.PATIENT_CANCEL_APPOINTMENT');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-4':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = $this->otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = $this->otherInfoContent_doctorCancelAdmissions($retData);
                break;

            case 'cancel-5':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = $this->otherInfoContent_doctorRescheduled($appointments, $retData);

                $infoText = \Config::get('constants.PATIENT_CANCEL_APPOINTMENT');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-6':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = $this->otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = $this->otherInfoContent_confirmRescheduled($appointments, $retData);

                $infoText = \Config::get('constants.PATIENT_CANCEL_APPOINTMENT');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'no');
                break;

            case 'cancel-7':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = $this->otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = $this->otherInfoContent_confirmRescheduled($appointments, $retData);
                $retData = $this->otherInfoContent_doctorCancelAdmissions($retData);
                break;

            /**
             * completed:
             * completed-1:最简正常流程
             * completed-2:改期后完成
             */
            case 'completed-1':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);

                $time = $appointments->completed_admissions_time;
                $infoText = \Config::get('constants.CONFIRM_ADMISSIONS');
                $retData = $this->copyTransformer($retData, $time, $infoText, null, 'pass');

                $retData = $this->otherInfoContent_completed($appointments, $retData);
                break;

            case 'completed-2':
                $retData = $this->otherInfoContent_alreadyPaid($appointments, $retData);
                $retData = $this->otherInfoContent_confirmAdmissions($appointments, $doctors, $retData);
                $retData = $this->otherInfoContent_doctorRescheduled($appointments, $retData);
                $retData = $this->otherInfoContent_confirmRescheduled($appointments, $retData);
                $retData = $this->otherInfoContent_completed($appointments, $retData);
                break;

            default:
                $retData = [];
                break;
        }

        return $retData;
    }

    /**
     * 医生取消约诊的文案段。
     *
     * @param $retData
     * @return mixed
     */
    private function otherInfoContent_doctorCancelAdmissions($retData)
    {
        $infoText = \Config::get('constants.DOCTOR_CANCEL_ADMISSIONS');
        return $this->copyTransformer($retData, null, $infoText, null, 'no');
    }

    /**
     * 医生确认改期的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private function otherInfoContent_confirmRescheduled($appointments, $retData)
    {
        $time = $appointments->confirm_rescheduled_time;
        $infoText = \Config::get('constants.CONFIRM_RESCHEDULED');
        return $this->copyTransformer($retData, $time, $infoText, null, 'pass');
    }

    /**
     * 患者已经支付的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private function otherInfoContent_alreadyPaid($appointments, $retData)
    {
        $payRecord = PayRecord::where('transaction_id', $appointments->transaction_id)->get()->first();
        $time = $payRecord->created_at->format('Y-m-d H:i:s');
        $infoText = \Config::get('constants.ALREADY_PAID');
        return $this->copyTransformer($retData, $time, $infoText, null, 'pass');
    }

    /**
     * 医生改期的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private function otherInfoContent_doctorRescheduled($appointments, $retData)
    {
        $time = $appointments->rescheduled_time;
        $infoText = \Config::get('constants.DOCTOR_RESCHEDULED');
        $infoOther = [[
            'name' => \Config::get('constants.RESCHEDULED_TIME'),
            'content' => $appointments->new_visit_time . ' ' . (($appointments->new_am_pm == 'am') ? '上午' : '下午')
        ]];
        return $this->copyTransformer($retData, $time, $infoText, $infoOther, 'time');
    }

    /**
     * 患者缴费后,医生确认约诊,等待面诊的文案段。
     *
     * @param $appointments
     * @param $doctors
     * @param $retData
     * @return mixed
     */
    private function otherInfoContent_confirmAdmissions($appointments, $doctors, $retData)
    {
        $time = $appointments->confirm_admissions_time;
        $infoText = \Config::get('constants.CONFIRM_ADMISSIONS_WAIT_FACE_CONSULTATION');
        $infoOther = $this->infoOther_faceConsultation($appointments, $doctors);
        return $this->copyTransformer($retData, $time, $infoText, $infoOther, 'notepad');
    }

    /**
     * 完成约诊的文案段。
     *
     * @param $appointments
     * @param $retData
     * @return mixed
     */
    private function otherInfoContent_completed($appointments, $retData)
    {
        $time = $appointments->updated_at->format('Y-m-d H:i:s');
        $infoText = \Config::get('constants.FACE_CONSULTATION_COMPLETE');
        return $this->copyTransformer($retData, $time, $infoText, null, 'completed');
    }

    /**
     * 面诊的附加信息段
     *
     * @param $appointments
     * @param $doctors
     * @return array
     */
    private function infoOther_faceConsultation($appointments, $doctors)
    {
        return [[
            'name' => \Config::get('constants.TREATMENT_TIME'),
            'content' => $appointments->visit_time . ' ' . (($appointments->am_pm == 'am') ? '上午' : '下午')
        ], [
            'name' => \Config::get('constants.TREATMENT_HOSPITAL'),
            'content' => $doctors->hospital
        ], [
            'name' => \Config::get('constants.SUPPLEMENT'),
            'content' => Hospital::where('id', $doctors->hospital_id)->get()->lists('address')->first()
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
    public function generateProgressStatus($status)
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
    public function beginText($appointments, $doctors)
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
    public function confirmLocumsText($appointments, $myId, $locumsDoctor = null)
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
    public function copyTransformer($retData, $time, $infoText, $infoOther, $type)
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
