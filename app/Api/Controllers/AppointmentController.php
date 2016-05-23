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
            'visit_time' => $request['time'],
            'am_pm' => $request['am_or_pm'],
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

        $appointments['time_line'] = $this->generateTimeLine($appointments, $doctors);
        $appointments['progress'] = $this->generateProgressStatus($appointments->status);

        return Transformer::appointmentsTransform($appointments, $doctors);
    }

    /**
     * 生成时间轴及其文案
     * 
     * @param $appointments
     * @param $doctors
     * @return array
     */
    public function generateTimeLine($appointments, $doctors)
    {
        $retData = array();

        switch ($appointments->status) {
            case 'wait-1':
                $time = $appointments->created_at->format('Y-m-d H:i:s');
                $infoText = $this->beginText($appointments, $doctors);
                $infoOther = [0 => [
                    'name' => \Config::get('constants.TREATMENT_TIME'),
                    'content' => $appointments->visit_time . ' ' . (($appointments->am_pm == 'am') ? '上午' : '下午')
                ]];
                $retData = $this->copyTransformer($retData, $time, $infoText, $infoOther, 'begin');

                $infoText = \Config::get('constants.WAIT_PAYMENT');
                $retData = $this->copyTransformer($retData, null, $infoText, null, 'wait');
                break;
            case 'wait-2':
                $retData = [];
                break;
            case 'wait-3':
                $retData = [];
                break;
            case 'wait-4':
                $retData = [];
                break;
            case 'wait-5':
                $retData = [];
                break;
            case '':
                $retData = [];
                break;
            default:
                $retData = [];
                break;
        }

        return $retData;
    }

    public function generateProgressStatus($status)
    {
        $retData = array();

        switch ($status) {
            case 'wait-1':
                $retData = [
                    'milestone' => '发起约诊',
                    'status' => '待付款'
                ];
                break;
            case 'wait-2':
                $retData = [];
                break;
            case 'wait-3':
                $retData = [];
                break;
            case 'wait-4':
                $retData = [];
                break;
            case 'wait-5':
                $retData = [];
                break;
            case '':
                $retData = [];
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
