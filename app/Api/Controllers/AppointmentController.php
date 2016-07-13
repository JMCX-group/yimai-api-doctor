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
use App\Api\Transformers\ReservationRecordTransformer;
use App\Api\Transformers\TimeLineTransformer;
use App\Api\Transformers\Transformer;
use App\Appointment;
use App\AppointmentMsg;
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

        /**
         * 发起约诊信息记录
         */
        $data = [
            'id' => $frontId . $nowId,
            'locums_id' => $user->id, //代理医生ID
            'patient_name' => $request['name'],
            'patient_phone' => $request['phone'],
            'patient_gender' => $request['sex'],
            'patient_age' => $request['age'],
            'patient_history' => $request['history'],
            'doctor_id' => $request['doctor'],
            'expect_visit_date' => $request['date'],
            'expect_am_pm' => $request['am_or_pm'],
            'status' => 'wait-1' //新建约诊之后,进入待患者付款阶段
        ];

        /**
         * 推送消息记录
         */
        $msgData = [
            'appointment_id' => $frontId . $nowId,
            'locums_id' => $user->id, //代理医生ID
            'locums_name' => $user->name, //代理医生姓名
            'patient_name' => $request['name'],
            'doctor_id' => $request['doctor'],
            'doctor_name' => User::find($request['doctor'])->first()->name,
            'status' => 'wait-1' //新建约诊之后,进入待患者付款阶段
        ];

        try {
            Appointment::create($data);
            AppointmentMsg::create($msgData);
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
    public function getReservationRecord()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $appointments = Appointment::where('appointments.locums_id', $user->id)
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->select('appointments.*', 'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($appointments->isEmpty()) {
//            return $this->response->noContent();
            return response()->json(['success' => ''], 204); //给肠媳适配。。
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

        return ['data' => ['wait' => $waitingForReply, 'already' => $alreadyReply]];
    }
}
