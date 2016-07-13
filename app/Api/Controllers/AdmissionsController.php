<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\AgreeAdmissionsRequest;
use App\Api\Requests\CompleteAdmissionsRequest;
use App\Api\Requests\RefusalAdmissionsRequest;
use App\Api\Transformers\AdmissionsRecordTransformer;
use App\Api\Transformers\TimeLineTransformer;
use App\Api\Transformers\Transformer;
use App\Appointment;
use App\AppointmentMsg;
use App\Hospital;
use App\User;

class AdmissionsController extends BaseController
{
    /**
     * 同意接诊。
     * 
     * @param AgreeAdmissionsRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function agreeAdmissions(AgreeAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-2') {
            $appointment->status = 'wait-3'; //医生确认接诊
            $appointment->visit_time = date('Y-m-d', strtotime($request['visit_time']));
            $amOrPm = date('H', strtotime($request['visit_time']));
            $appointment->am_pm = $amOrPm <= 12 ? 'am' : 'pm';
            $appointment->supplement = (isset($request['supplement']) && $request['supplement'] != null) ? $request['supplement'] : ''; //补充说明
            $appointment->remark = (isset($request['remark']) && $request['remark'] != null) ? $request['remark'] : ''; //附加信息

            $appointment->confirm_admissions_time = date('Y-m-d H:i:s'); //确认接诊时间
            $appointment->save();

            return $this->getDetailInfo($request['id']);
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
    public function refusalAdmissions(RefusalAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-2') {
            $appointment->status = 'close-3'; //医生拒绝接诊
            $appointment->refusal_reason = $request['reason'];

            $appointment->confirm_admissions_time = date('Y-m-d H:i:s'); //确认接诊时间
            $appointment->save();

            return $this->getDetailInfo($request['id']);
        } else {
            return response()->json(['message' => '状态错误'], 400);
        }
    }

    /**
     * 转诊。
     * 
     * @param RefusalAdmissionsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transferAdmissions(RefusalAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-2') {
            $appointment->doctor_id = $request['doctor_id']; //修改医生信息
            $appointment->save();
            
            /**
             * 推送消息记录
             */
            $msgData = [
                'appointment_id' => $request['id'],
                'locums_id' => $appointment->locums_id, //代理医生ID
                'locums_name' => User::find($appointment->locums_id)->first()->name, //代理医生姓名
                'patient_name' => $appointment->patient_name,
                'doctor_id' => $request['doctor_id'],
                'doctor_name' => User::find($request['doctor_id'])->first()->name,
                'status' => 'wait-2' //患者已付款，待医生确认
            ];
            
            AppointmentMsg::create($msgData);

            return response()->json(['success' => ''], 204); //给肠媳适配。。
        } else {
            return response()->json(['message' => '状态错误'], 400);
        }
    }

    /**
     * 完成接诊/面诊。
     *
     * @param CompleteAdmissionsRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function completeAdmissions(CompleteAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-5') {
            if ($appointment->new_am_pm == null || $appointment->new_am_pm == '') {
                $appointment->status = 'completed-1'; //正常完成面诊
            } else {
                $appointment->status = 'completed-2'; //改期后完成面诊
            }

            $appointment->refusal_reason = $request['reason'];
            $appointment->completed_admissions_time = date('Y-m-d H:i:s'); //完成面诊时间
            $appointment->save();

            return $this->getDetailInfo($request['id']);
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
    public function rescheduledAdmissions(AgreeAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);
        $appointment->status = 'wait-4'; //医生改期
        $appointment->rescheduled_time = date('Y-m-d H:i:s', strtotime($request['visit_time']));
        $appointment->save();

        return $this->getDetailInfo($request['id']);
    }

    /**
     * 医生取消约诊。
     * 
     * @param RefusalAdmissionsRequest $request
     * @return array|mixed
     */
    public function cancelAdmissions(RefusalAdmissionsRequest $request)
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
        $appointment->confirm_admissions_time = date('Y-m-d H:i:s'); //确认接诊时间
        $appointment->save();

        return $this->getDetailInfo($request['id']);
    }

    /**
     * 我的接诊。
     *
     * @return array|\Dingo\Api\Http\Response|mixed
     */
    public function getAdmissionsRecord()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $appointments = Appointment::where('appointments.doctor_id', $user->id)
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.locums_id')
            ->leftJoin('patients', 'patients.id', '=', 'appointments.patient_id')
            ->select('appointments.*',
                'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth',
                'patients.avatar as patient_avatar')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($appointments->isEmpty()) {
//                    return $this->response->noContent();
            return response()->json(['success' => ''], 204); //给肠媳适配。。
        }

        $hospital = Hospital::find($user->hospital_id)->name;
        $waitingForReply = array();
        $waitingForComplete = array();
        $completed = array();
        foreach ($appointments as $appointment) {
            $appointment['hospital'] = $hospital;
            if ($appointment['status'] == 'wait-2') {
                array_push($waitingForReply, AdmissionsRecordTransformer::admissionsTransform($appointment));
            } elseif (in_array($appointment['status'], array('wait-3', 'wait-4', 'wait-5'))) {
                array_push($waitingForComplete, AdmissionsRecordTransformer::admissionsTransform($appointment));
            } elseif ($appointment['status'] != 'wait-1') {
                array_push($completed, AdmissionsRecordTransformer::admissionsTransform($appointment));
            }
        }

        return ['data' => [
            'wait_reply' => $waitingForReply,
            'wait_complete' => $waitingForComplete,
            'completed' => $completed,
        ]];
    }

    /**
     * @param $id
     * @return array|mixed
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

        /**
         * 查询代约医生的信息:
         */
        $doctors = User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.title',
            'hospitals.name AS hospital', 'dept_standards.name AS dept')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->where('doctors.id', $appointments->locums_id)
            ->get()
            ->first();

        $appointments['time_line'] = TimeLineTransformer::generateTimeLine($appointments, $doctors, $user->id, $doctors);

        $appointments['progress'] = TimeLineTransformer::generateProgressStatus($appointments->status);

        return Transformer::appointmentsTransform($appointments, $doctors);
    }
}
