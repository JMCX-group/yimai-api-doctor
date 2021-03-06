<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Helper\MsgAndNotification;
use App\Api\Helper\SmsContent;
use App\Api\Requests\AppointmentIdRequest;
use App\Api\Requests\AppointmentRequest;
use App\Api\Requests\AppointmentUpdateRequest;
use App\Api\Requests\RefusalAdmissionsRequest;
use App\Api\Transformers\ReservationRecordTransformer;
use App\Api\Transformers\TimeLineTransformer;
use App\Api\Transformers\Transformer;
use App\Appointment;
use App\Patient;
use App\User;
use App\Api\Helper\SaveImage;
use Tymon\JWTAuth\Exceptions\JWTException;

class AppointmentController extends BaseController
{
    /**
     * 代约医生确认代约
     *
     * @param AppointmentUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function update(AppointmentUpdateRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $appointmentId = $request['id'];
        $doctorId = $request['doctor'];

        $appointment = Appointment::find($appointmentId);
        $doctor = User::find($doctorId);

        /**
         * 更新的约诊信息：
         */
        $appointment->confirm_locums_time = date('Y-m-d H:i:s'); //确认代约时间
        $appointment->price = $doctor->fee;
        $appointment->doctor_id = $doctorId;
        $appointment->status = 'wait-1';//预约医生之后,进入待患者付款阶段

        try {
            if ($appointment->save()) {
                MsgAndNotification::sendAppointmentsMsg(Appointment::find($appointmentId)); //推送消息
                MsgAndNotification::pushAppointmentMsg_patient(null, $appointment); //向患者端推送消息

                return response()->json(['success' => ''], 204);
            } else {
                return response()->json(['message' => '保存失败'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    /**
     * 拒绝代约
     *
     * @param RefusalAdmissionsRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function refusal(RefusalAdmissionsRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-0' && $appointment->locums_id == $user->id) {
            $appointment->status = 'close-0'; //医生拒绝代约
            $appointment->refusal_reason = $request['reason'];
            $appointment->doctor_refusal_time = date('Y-m-d H:i:s'); //确认接诊时间

            try {
                if ($appointment->save()) {
                    MsgAndNotification::pushAppointmentMsg_patient(null, $appointment); //向患者端推送消息

                    return $this->getDetailInfo($request['id']);
                } else {
                    return response()->json(['message' => '保存失败'], 500);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }
        } else {
            return response()->json(['message' => '状态错误'], 400);
        }
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
        $appointmentId = $frontId . $nowId;

        /**
         * 时间过滤：
         */
        $expectVisitDate = $request['date'];
        if (substr($expectVisitDate, strlen($expectVisitDate) - 1) == ',') {
            $expectVisitDate = substr($expectVisitDate, 0, strlen($expectVisitDate) - 1);
        }
        $expectAmPm = $request['am_or_pm'];
        if (substr($expectAmPm, strlen($expectAmPm) - 1) == ',') {
            $expectAmPm = substr($expectAmPm, 0, strlen($expectAmPm) - 1);
        }

        /**
         * 发起约诊信息记录
         */
        $doctor = User::find($request['doctor']);
        $patient = Patient::where('phone', $request['phone'])->first();
        $data = [
            'id' => $appointmentId,
            'locums_id' => $user->id, //代理医生ID
            'patient_name' => $request['name'],
            'patient_phone' => $request['phone'],
            'patient_gender' => $request['sex'],
            'patient_age' => $request['age'],
            'patient_history' => $request['history'],
            'doctor_id' => $request['doctor'],
            'patient_id' => (isset($patient->id)) ? $patient->id : null,
            'doctor_or_patient' => 'd', //医生发起的约诊
            'expect_visit_date' => $expectVisitDate,
            'expect_am_pm' => $expectAmPm,
            'price' => $doctor->fee,
            'status' => 'wait-1' //新建约诊之后,进入待患者付款阶段
        ];

        try {
            $appointment = Appointment::create($data);
            $appointment->id = $appointmentId;

            /**
             * 是否为已注册患者
             * 注册患者发送单播通知；未注册患者需要发送短信
             */
            if (isset($patient->id)) {
                MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息
                MsgAndNotification::pushAppointmentMsg_patient($patient, $appointment); //向患者端推送消息
            } else {
                SmsContent::sendSMS_newPatient($user, $doctor, $request['phone']); //向患者端发送短信
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        return ['id' => $appointmentId];
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
        $imgUrl = SaveImage::appointment($appointment->id, $request->file('img'));

        if (strlen($appointment->patient_imgs) > 0) {
            $appointment->patient_imgs .= ',' . $imgUrl;
        } else {
            $appointment->patient_imgs = $imgUrl;
        }

        $appointment->save();

        return ['url' => $imgUrl];
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
            ->first();

        if (empty($appointments)) {
            return response()->json(['message' => '没有该订单'], 404);
        }

        $doctors = User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.title',
            'hospitals.name AS hospital', 'dept_standards.name AS dept')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->where('doctors.id', $appointments->doctor_id)
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
                ->first();

            $appointments['time_line'] = TimeLineTransformer::generateTimeLine($appointments, $doctors, $user->id, $locumsDoctor);
        } else {
            $appointments['time_line'] = TimeLineTransformer::generateTimeLine($appointments, $doctors, $user->id);
        }

        $appointments['progress'] = TimeLineTransformer::generateProgressStatus($appointments->status);

        return Transformer::appointmentsTransform($appointments, $doctors);
    }

    /**
     * 约诊记录。
     *
     * @return array|mixed
     */
    public function myList()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        /**
         * 获取返回信息：
         */
        $appointments = Appointment::where('appointments.locums_id', $user->id)
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->select('appointments.*', 'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json(['success' => ''], 204);
        }

        $waitingForReply = array();
        $alreadyReply = array();
        foreach ($appointments as $appointment) {
            if (strstr($appointment['status'], 'wait')) {
                array_push($waitingForReply, ReservationRecordTransformer::appointmentTransform($appointment));
            } else {
                array_push($alreadyReply, ReservationRecordTransformer::appointmentTransform($appointment));
            }
        }

        $data = [
            'wait' => $waitingForReply,
            'already' => $alreadyReply
        ];

        return response()->json(compact('data'));
    }
}
