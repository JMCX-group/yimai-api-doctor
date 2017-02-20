<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Helper\MsgAndNotification;
use App\Api\Requests\AgreeAdmissionsRequest;
use App\Api\Requests\CompleteAdmissionsRequest;
use App\Api\Requests\RefusalAdmissionsRequest;
use App\Api\Requests\RefusalRequest;
use App\Api\Transformers\AdmissionsRecordTransformer;
use App\Api\Transformers\TimeLineTransformer;
use App\Api\Transformers\Transformer;
use App\Appointment;
use App\AppointmentFee;
use App\Doctor;
use App\Hospital;
use App\Patient;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdmissionsController extends BaseController
{
    /**
     * 同意接诊。
     *
     * @param AgreeAdmissionsRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function agree(AgreeAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-2') {
            $appointment->status = 'wait-3'; //医生确认接诊
            $appointment->visit_time = date('Y-m-d H:i:s', strtotime($request['visit_time']));
            $amOrPm = date('H', strtotime($request['visit_time']));
            $appointment->am_pm = $amOrPm <= 12 ? 'am' : 'pm';
            $appointment->supplement = (isset($request['supplement']) && $request['supplement'] != null) ? $request['supplement'] : ''; //补充说明
            $appointment->remark = (isset($request['remark']) && $request['remark'] != null) ? $request['remark'] : ''; //附加信息

            $appointment->confirm_admissions_time = date('Y-m-d H:i:s'); //确认接诊时间

            try {
                if ($appointment->save()) {
                    MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息
                    MsgAndNotification::pushAppointmentMsg_patient(null, $appointment); //向患者端推送消息

                    return $this->detail($request['id']);
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
     * 拒绝接诊。
     *
     * @param RefusalAdmissionsRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function refusal(RefusalAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-2') {
            $appointment->status = 'close-3'; //医生拒绝接诊
            $appointment->refusal_reason = $request['reason'];

            $appointment->doctor_refusal_time = date('Y-m-d H:i:s'); //确认接诊时间

            try {
                if ($appointment->save()) {
                    $this->paymentStatusRefresh($appointment->id); //刷新支付状态

                    MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息
                    MsgAndNotification::pushAppointmentMsg_patient(null, $appointment); //向患者端推送消息

                    return $this->detail($request['id']);
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
     * 转诊
     *
     * @param RefusalRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function transfer(RefusalRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $appointment = Appointment::find($request['id']);
        if ($appointment->is_transfer == 1) {
            return response()->json(['message' => '已经转诊'], 400);
        }

        if ($appointment->status == 'wait-2') {
            /**
             * 本约诊结束：
             */
            $appointment->status = 'close-5'; //医生转诊,约诊关闭
            $appointment->doctor_transfer_time = date('Y-m-d H:i:s');
            try {
                if ($appointment->save()) {
                    MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息
                } else {
                    return response()->json(['message' => '保存失败'], 500);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }

            /**
             * 新开约诊：
             */
            $doctorId = $request['doctor_id'];
            $doctor = User::find($doctorId);
            $newAppointmentId = $this->newAppointmentId('66'); //66是转诊
            $data = [
                'id' => $newAppointmentId,
                'locums_id' => $user->id, //代理医生ID
                'patient_name' => $appointment->patient_name,
                'patient_phone' => $appointment->patient_phone,
                'patient_gender' => $appointment->gender,
                'patient_age' => $appointment->patient_age,
                'patient_history' => $appointment->patient_history,
                'patient_imgs' => $appointment->patient_imgs,
                'doctor_id' => $doctorId,
                'patient_id' => $appointment->patient_id,
                'doctor_or_patient' => 'd', //医生发起的约诊
                'expect_visit_date' => $appointment->expect_visit_date,
                'expect_am_pm' => $appointment->expect_am_pm,
                'price' => $doctor->fee,
                'is_transfer' => 1, //已被转诊
                'transfer_id' => $appointment->id,
                'is_pay' => 1,
                'status' => 'wait-2'
            ];

            /**
             * 获取旧的约诊支付情况和部署新的支付情况：
             */
            $oldAppointmentFeeData = AppointmentFee::where('appointment_id', $appointment->id)->first();

            $appointmentFeeData = [
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id,
                'locums_id' => $user->id,
                'appointment_id' => $newAppointmentId,
                'total_fee' => $oldAppointmentFeeData->total_fee,
                'reception_fee' => $oldAppointmentFeeData->reception_fee,
                'platform_fee' => $oldAppointmentFeeData->platform_fee,
                'intermediary_fee' => 0, //中介费
                'guide_fee' => 0, //导诊费
                'default_fee_rate' => 0, //违约费率
                'status' => 'paid' //资金状态：paid（已支付）、completed（已完成）、cancelled（已取消）
            ];

            /**
             * 老的支付信息置0
             */
            $oldAppointmentFeeData->total_fee = 0;
            $oldAppointmentFeeData->reception_fee = 0;
            $oldAppointmentFeeData->platform_fee = 0;
            $oldAppointmentFeeData->save();

            try {
                $newAppointment = Appointment::create($data);
                $newAppointment->id = $newAppointmentId;
                AppointmentFee::create($appointmentFeeData);

                MsgAndNotification::sendAppointmentsMsg($newAppointment, 'wait-1'); //推送消息,wait-1
                MsgAndNotification::sendAppointmentsMsg($newAppointment); //推送消息,wait-2
                MsgAndNotification::pushAppointmentMsg_patient(null, $appointment, null, null, true); //向患者端推送消息；通知患者换订单了

                /**
                 * 给新的接诊医生推送消息：
                 */
                MsgAndNotification::pushAppointmentMsg_doctor($doctor, $newAppointment); //向医生端推送消息

                /**
                 * 返回旧的约诊信息给当前医生：
                 */
                return self::appointmentDetailInfo($appointment->id, $user->id);
            } catch (JWTException $e) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }
        } else {
            return response()->json(['message' => '状态错误'], 400);
        }
    }

    /**
     * 生成新的约诊订单号
     *
     * @param string $front
     * @return string
     */
    public function newAppointmentId($front='01')
    {
        /**
         * 计算预约码做ID.
         * 规则:01-99 . 年月日各两位长 . 0001-9999
         * 66是转诊
         */
        $frontId = $front . date('ymd');
        $lastId = Appointment::where('id', 'like', $frontId . '%')
            ->orderBy('id', 'desc')
            ->lists('id');
        if ($lastId->isEmpty()) {
            $nowId = '0001';
        } else {
            $lastId = intval(substr($lastId[0], 8));
            $nowId = str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        }

        return $frontId . $nowId;
    }

    /**
     * 完成接诊/面诊。
     *
     * @param CompleteAdmissionsRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function complete(CompleteAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-3' || $appointment->status == 'wait-5') {
            if ($appointment->new_am_pm == null || $appointment->new_am_pm == '') {
                $appointment->status = 'completed-1'; //正常完成面诊
            } else {
                $appointment->status = 'completed-2'; //改期后完成面诊
            }

            $appointment->completed_admissions_time = date('Y-m-d H:i:s'); //完成面诊时间

            try {
                if ($appointment->save()) {
                    MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息
                    MsgAndNotification::pushAppointmentMsg_patient(null, $appointment); //向患者端推送消息

                    return $this->detail($request['id']);
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
     * 医生改期。
     *
     * @param AgreeAdmissionsRequest $request
     * @return array|mixed
     */
    public function rescheduled(AgreeAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);
        $appointment->status = 'wait-4'; //医生改期
        $appointment->rescheduled_time = date('Y-m-d H:i:s');
        $appointment->new_visit_time = date('Y-m-d H:i:s', strtotime($request['visit_time']));
        $amOrPm = date('H', strtotime($request['visit_time']));
        $appointment->new_am_pm = $amOrPm <= 12 ? 'am' : 'pm';

        try {
            if ($appointment->save()) {
                MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息
                MsgAndNotification::pushAppointmentMsg_patient(null, $appointment); //向患者端推送消息

                return $this->detail($request['id']);
            } else {
                return response()->json(['message' => '保存失败'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    /**
     * 医生取消约诊。
     *
     * @param RefusalAdmissionsRequest $request
     * @return array|mixed
     */
    public function cancel(RefusalAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->new_am_pm == null || $appointment->new_am_pm == '') {
            $appointment->status = 'cancel-2'; //医生取消约诊
        } else {
            if ($appointment->confirm_rescheduled_time == null || strtotime($appointment->confirm_rescheduled_time) == strtotime('0000-00-00 00:00:00')) {
                $appointment->status = 'cancel-4'; //医生改期之后,医生取消约诊    
            } else {
                $appointment->status = 'cancel-7'; //医生改期之后,患者确认之后,医生取消约诊;
            }
        }

        $appointment->refusal_reason = $request['reason'];
        $appointment->doctor_cancel_time = date('Y-m-d H:i:s'); //医生取消接诊时间

        try {
            if ($appointment->save()) {
                $this->paymentStatusRefresh($appointment->id); //刷新支付状态

                MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息
                MsgAndNotification::pushAppointmentMsg_patient(null, $appointment); //向患者端推送消息

                return $this->detail($request['id']);
            } else {
                return response()->json(['message' => '保存失败'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    /**
     * 医生在取消或拒绝时给患者退款
     *
     * @param $appointmentId
     */
    public function paymentStatusRefresh($appointmentId)
    {
        AppointmentFee::where('appointment_id', $appointmentId)
            ->update([
                'total_fee' => 0,
                'reception_fee' => 0,
                'platform_fee' => 0,
                'intermediary_fee' => 0,
                'guide_fee' => 0,
                'default_fee_rate' => 0,
                'status' => 'cancelled', //资金状态：paid（已支付）、completed（已完成）、cancelled（已取消）
                'time_expire' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * 我的接诊。
     *
     * @return array|\Dingo\Api\Http\Response|mixed
     */
    public function myList()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        /**
         * 更新已付款，48小时未确认的：
         */
        Appointment::where('doctor_id', $user->id)
            ->where('status', 'wait-2')
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 48 * 3600))
            ->update(['status' => 'close-2']); //close-2: 医生过期未接诊,约诊关闭

        /**
         * 获取返回信息：
         */
        $appointments = Appointment::where('appointments.doctor_id', $user->id)
            ->select('appointments.*',
                'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth',
                'patients.avatar as patient_avatar')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.locums_id')
            ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json(['success' => ''], 204);
        }

        $hospital = Hospital::find($user->hospital_id)->name;

        $waitingForReply = array();
        $waitingForComplete = array();
        $completed = array();

        foreach ($appointments as $appointment) {
            $appointment['hospital'] = $hospital;

            switch ($appointment['status']) {
                //wait-0需要平台受理，wait-1需要患者付款
                case 'wait-0':
                case 'wait-1':
                    break;
                case 'wait-2':
                    array_push($waitingForReply, AdmissionsRecordTransformer::admissionsTransform($appointment));
                    break;
                case 'wait-3':
                case 'wait-4':
                case 'wait-5':
                    array_push($waitingForComplete, AdmissionsRecordTransformer::admissionsTransform($appointment));
                    break;
                default:
                    array_push($completed, AdmissionsRecordTransformer::admissionsTransform($appointment));
                    break;
            }
        }

        $data = [
            'wait_reply' => $waitingForReply,
            'wait_complete' => $waitingForComplete,
            'completed' => $completed
        ];

        return response()->json(compact('data'));
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public function detail($id)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        return self::appointmentDetailInfo($id, $user->id);
    }

    /**
     * 约诊信息生成
     *
     * @param $id
     * @param $userId
     * @return array
     */
    public static function appointmentDetailInfo($id, $userId)
    {
        $appointments = Appointment::where('appointments.id', $id)
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.locums_id')
            ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id')
            ->select('appointments.*', 'doctors.name as locums_name', 'patients.avatar as patient_avatar')
            ->first();

        /**
         * 查询医生的信息:
         */
        $doctors = User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.title',
            'hospitals.name AS hospital', 'dept_standards.name AS dept')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->where('doctors.id', $appointments->doctor_id)
            ->first();

        /**
         * 查询代约医生的信息:
         */
        $locumsDoctors = User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.title',
            'hospitals.name AS hospital', 'dept_standards.name AS dept')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->where('doctors.id', $appointments->locums_id)
            ->first();

        $appointments['time_line'] = TimeLineTransformer::generateTimeLine($appointments, $doctors, $userId, $locumsDoctors);
        $appointments['progress'] = TimeLineTransformer::generateProgressStatus($appointments->status);

        return Transformer::appointmentsTransform($appointments, $locumsDoctors);
    }
}
