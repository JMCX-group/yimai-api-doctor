<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Helper\Sms;
use App\Api\Requests\AppointmentIdRequest;
use App\Api\Requests\AppointmentRequest;
use App\Api\Requests\AppointmentUpdateRequest;
use App\Api\Transformers\ReservationRecordTransformer;
use App\Api\Transformers\TimeLineTransformer;
use App\Api\Transformers\Transformer;
use App\Appointment;
use App\AppointmentMsg;
use App\Patient;
use App\User;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class AppointmentController extends BaseController
{
    public function index()
    {

    }

    /**
     * 代约医生信息确认
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

        $appointment = Appointment::find($request['id']);

        $doctor = User::getDoctorAllInfo($request['doctor']);

        /**
         * 更新的约诊信息：
         */
        $appointment->price = $doctor->fee;
        $appointment->doctor_id = $request['doctor'];
        $appointment->status = 'wait-1';//预约医生之后,进入待患者付款阶段

        try {
            if ($appointment->save()) {
                return response()->json(['success' => ''], 204);
            } else {
                return response()->json(['message' => '保存失败'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
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
        $doctor = User::getDoctorAllInfo($request['doctor']);
        $data = [
            'id' => $frontId . $nowId,
            'locums_id' => $user->id, //代理医生ID
            'patient_name' => $request['name'],
            'patient_phone' => $request['phone'],
            'patient_gender' => $request['sex'],
            'patient_age' => $request['age'],
            'patient_history' => $request['history'],
            'doctor_id' => $request['doctor'],
            'expect_visit_date' => $expectVisitDate,
            'expect_am_pm' => $expectAmPm,
            'price' => $doctor->fee,
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
            'doctor_name' => $doctor->name,
            'status' => 'wait-1' //新建约诊之后,进入待患者付款阶段
        ];

        try {
            Appointment::create($data);
            AppointmentMsg::create($msgData);

            /**
             * 是否为已注册患者
             * 注册患者发送单播通知；未注册患者需要发送短信
             */
            $patient = Patient::where('phone', $request['phone'])->first();
            if ($patient->isEmpty()) {
                $this->sendSMS($user, $doctor, $request['phone']);
            } else {
                if ($patient->device_token != '' && $patient->device_token != null) {
                    /**
                     * 向患者端推送消息
                     */
                    require(dirname(dirname(__FILE__)) . '/Helper/UmengNotification/NotificationPush.php');
                    //患者端企业版
                    $push = new \NotificationPush('58770533c62dca6297001b7b', 'mnbtm9nu5v2cw5neqbxo6grqsuhxg1o8');
                    //患者端AppStore
//            $push = new \NotificationPush('587704b3310c934edb002251', 'mngbtbi7lj0y8shlmdvvqdkek9k3hfin');
                    $pushResult = $push->sendIOSUnicast($patient->device_token, '您有新的约诊订单需要支付', 'appointment');

                    if ($pushResult['result'] == false) {
                        Log::info('push-appointment-patient', ['context' => $pushResult['message']]);
                    }
                }
            }
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
        $domain = \Config::get('constants.DOMAIN');
        $destinationPath =
            \Config::get('constants.CASE_HISTORY_SAVE_PATH') .
            date('Y') . '/' . date('m') . '/' .
            $appointmentId . '/';
        $filename = time() . '.jpg';

        $imgFile->move($destinationPath, $filename);

        $fullPath = $destinationPath . $filename;
        $newPath = str_replace('.jpg', '_thumb.jpg', $fullPath);

        Image::make($fullPath)->encode('jpg', 30)->save($newPath); //按30的品质压缩图片

        return $domain . '/' . $newPath;
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

        /**
         * 更新过期未支付的：
         */
        Appointment::where('locums_id', $user->id)
            ->where('is_pay', '0')
            ->where('status', 'wait-1')
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 12 * 3600))
            ->update(['status' => 'close-1']); //close-1: 待患者付款，关闭

        /**
         * 更新已付款，48小时未确认的：
         */
        Appointment::where('locums_id', $user->id)
            ->where('status', 'wait-2')
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 48 * 3600))
            ->update(['status' => 'close-2']); //close-2: 医生过期未接诊,约诊关闭

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

    /**
     * 发送短信
     *
     * @param $user
     * @param $doctor
     * @param $phone
     */
    public function sendSMS($user, $doctor, $phone)
    {
        $sms = new Sms();
        //文案：
        $txt = '【医者脉连】' .
            $user->name . '医生刚刚通过“医者脉连”平台为您预约' .
            $doctor->hospital .
            $doctor->dept .
            $doctor->name . '医师的面诊，约诊费约为' .
            $doctor->fee . '元，请在12小时内安装“医者脉连-看专家”客户端进行确认。下载地址：http://pre.im/PHMF 。请确保使用本手机号码进行注册和登陆以便查看该笔预约。';
        $sms->sendSMS($phone, $txt);
    }
}
