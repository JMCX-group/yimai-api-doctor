<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\AgreeAdmissionsRequest;
use App\Api\Transformers\AdmissionsRecordTransformer;
use App\Api\Transformers\TimeLineTransformer;
use App\Api\Transformers\Transformer;
use App\Appointment;
use App\User;

class AdmissionsController extends BaseController
{
    /**
     * 同意/拒绝接诊。
     * 
     * @param AgreeAdmissionsRequest $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function agreeOrRefusalAdmissions(AgreeAdmissionsRequest $request)
    {
        $appointment = Appointment::find($request['id']);

        if ($appointment->status == 'wait-2') {
            if (isset($request['status']) && $request['status'] == 'no') {
                $appointment->status = 'close-3'; //医生拒绝接诊
                $appointment->refusal_reason = $request['reason'];
            } else {
                $appointment->status = 'wait-3'; //医生确认接诊
                $appointment->visit_time = date('Y-m-d', strtotime($request['visit_time']));
                $amOrPm = date('H', strtotime($request['visit_time']));
                $appointment->am_pm = $amOrPm <= 12 ? 'am' : 'pm';
                $appointment->supplement = (isset($request['supplement']) && $request['supplement'] != null) ? $request['supplement'] : ''; //补充说明
                $appointment->remark = (isset($request['remark']) && $request['remark'] != null) ? $request['remark'] : ''; //附加信息
            }

            $appointment->confirm_admissions_time = date('Y-m-d H:i:s'); //确认接诊时间
            $appointment->save();

            return $this->getDetailInfo($request['id']);
        } else {
            return response()->json(['message' => '状态错误'], 400);
        }
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
            return $this->response->noContent();
        }

        $waitingForReply = array();
        $waitingForComplete = array();
        $completed = array();
        foreach ($appointments as $appointment) {
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
