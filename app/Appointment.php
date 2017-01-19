<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Appointment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'appointments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'locums_id',
        'confirm_locums_time',
        'patient_name',
        'patient_phone',
        'patient_gender',
        'patient_age',
        'patient_history',
        'patient_imgs',
        'doctor_id',
        'patient_id',
        'patient_demand_doctor_name',
        'patient_demand_hospital',
        'patient_demand_dept',
        'patient_demand_title',
        'request_mode',
        'platform_or_doctor',
        'doctor_or_patient',
        'expect_visit_date',
        'expect_am_pm',
        'visit_time',
        'am_pm',
        'supplement',
        'remark',
        'refusal_reason',
        'deposit',
        'price',
        'transaction_id',
        'confirm_admissions_time',
        'completed_rescheduled_time',
        'rescheduled_time',
        'new_visit_time',
        'new_am_pm',
        'confirm_rescheduled_time',
        'is_pay',
        'status'
    ];

    /**
     * 查询过期（24小时）未受理代约的
     *
     * @return mixed
     */
    public static function getOverdueAcceptedList()
    {
        return Appointment::where('status', 'wait-0')
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 24 * 3600))
            ->get();
    }

    /**
     * 查询过期（12小时）未支付的
     *
     * @return mixed
     */
    public static function getOverduePaymentList()
    {
        return Appointment::where('is_pay', '0')
            ->where('status', 'wait-1')
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 12 * 3600))
            ->get();
    }

    /**
     * 查询过期（48小时）未接诊的
     *
     * @return mixed
     */
    public static function getOverdueNotAdmissionsList()
    {
        return Appointment::where('status', 'wait-2')
            ->where('updated_at', '<', date('Y-m-d H:i:s', time() - 48 * 3600))
            ->get();
    }

    /**
     * 获取全部wait-1和已支付状态的订单
     *
     * @return mixed
     */
    public static function getPaidNoCallbackList()
    {
        return Appointment::where('status', 'wait-1')
            ->where('is_pay', '1')
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function getMyDoctors($id)
    {
        /**
         * 获取约诊成功的医生列表：
         */
        $ret = DB::select(
            "select distinct `doctor_id` from `appointments` where `patient_id` = '$id' AND (`status`='completed-1' OR `status`='completed-2')"
        );

        /**
         * 获取扫码添加的医生列表：
         */
        $patientMyDoctors = Patient::select('my_doctors')->where('id', $id)->get()->toArray();

        /**
         * 去重：
         */
        $myDoctors = explode(',', $patientMyDoctors[0]['my_doctors']);
        $tmpIdArr = array();
        foreach ($ret as $item) {
            if (!in_array($item->doctor_id, $myDoctors)) {
                array_push($tmpIdArr, $item->doctor_id);
            }
        }
        $retArr = array_merge($tmpIdArr, $myDoctors);

        return $retArr;
    }

    /**
     * 获取约单数量最多的十名医生ID
     *
     * @return mixed
     */
    public static function getTop10()
    {
        return Appointment::select(DB::raw('count(*) as count, doctor_id'))
            ->where('doctor_id', '!=', '')
            ->groupBy('doctor_id')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get()
            ->lists('doctor_id')
            ->toArray();
    }

    /**
     * 获得wait的
     *
     * @return mixed
     */
    public static function getWait()
    {
        return Appointment::select(
            'appointments.id', 'appointments.locums_id', 'appointments.doctor_id',
            'appointments.patient_name', 'appointments.patient_phone', 'appointments.patient_history',
            'appointments.price', 'appointments.status',
            'appointments.created_at', 'appointments.updated_at',
            'doctors.name AS doctor')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->whereIn('appointments.status', ['wait-0', 'wait-1', 'wait-2', 'wait-3', 'wait-4', 'wait-5'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }

    /**
     * 获得close和cancel的
     *
     * @return mixed
     */
    public static function getCloseAndCancel()
    {
        return Appointment::select(
            'appointments.id', 'appointments.locums_id', 'appointments.doctor_id',
            'appointments.patient_name', 'appointments.patient_phone', 'appointments.patient_history',
            'appointments.price', 'appointments.status',
            'appointments.created_at', 'appointments.updated_at',
            'doctors.name AS doctor')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->whereIn('appointments.status', ['close-1', 'close-2', 'close-3', 'cancel-1', 'cancel-2', 'cancel-3', 'cancel-4', 'cancel-5', 'cancel-6', 'cancel-7'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }

    /**
     * 获得completed的
     *
     * @return mixed
     */
    public static function getCompleted()
    {
        return Appointment::select(
            'appointments.id', 'appointments.locums_id', 'appointments.doctor_id',
            'appointments.patient_name', 'appointments.patient_phone', 'appointments.patient_history',
            'appointments.price', 'appointments.status',
            'appointments.created_at', 'appointments.updated_at',
            'doctors.name AS doctor')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->whereIn('appointments.status', ['completed-1', 'completed-2'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }

    /**
     * 获取平台代约
     *
     * @param $status
     * @return mixed
     */
    public static function getPlatform($status)
    {
        return Appointment::select(
            'appointments.id', 'appointments.locums_id', 'appointments.doctor_id',
            'appointments.patient_name', 'appointments.patient_phone', 'appointments.patient_history',
            'appointments.price', 'appointments.status',
            'appointments.created_at', 'appointments.updated_at',
            'doctors.name AS doctor')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->where('appointments.platform_or_doctor', 'p')
            ->where('appointments.status', $status)
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }

    /**
     * 获取已完成平台代约
     *
     * @return mixed
     */
    public static function getPlatform_processing()
    {
        return Appointment::select(
            'appointments.id', 'appointments.locums_id', 'appointments.doctor_id',
            'appointments.patient_name', 'appointments.patient_phone', 'appointments.patient_history',
            'appointments.price', 'appointments.status',
            'appointments.created_at', 'appointments.updated_at',
            'doctors.name AS doctor')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->where('appointments.platform_or_doctor', 'p')
            ->whereIn('appointments.status', ['wait-0', 'wait-1', 'wait-2', 'wait-3', 'wait-4', 'wait-5',])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }

    /**
     * 获取已完成平台代约
     *
     * @return mixed
     */
    public static function getPlatform_completed()
    {
        return Appointment::select(
            'appointments.id', 'appointments.locums_id', 'appointments.doctor_id',
            'appointments.patient_name', 'appointments.patient_phone', 'appointments.patient_history',
            'appointments.price', 'appointments.status',
            'appointments.created_at', 'appointments.updated_at',
            'doctors.name AS doctor')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->where('appointments.platform_or_doctor', 'p')
            ->whereIn('appointments.status', [
                'close-1', 'close-2', 'close-3',
                'cancel-1', 'cancel-2', 'cancel-3', 'cancel-4', 'cancel-5', 'cancel-6', 'cancel-7',
                'completed-1', 'completed-2'
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }

    /**
     * @param $appointmentId
     * @return mixed
     */
    public static function getAllDetail($appointmentId)
    {
        return Appointment::select(
            'appointments.id', 'appointments.locums_id', 'appointments.doctor_id',
            'appointments.patient_name', 'appointments.patient_phone', 'appointments.patient_history',
            'appointments.patient_demand_doctor_name', 'appointments.patient_demand_hospital', 'appointments.patient_demand_dept', 'appointments.patient_demand_title',
            'appointments.price', 'appointments.status',
            'appointments.created_at', 'appointments.updated_at',
            'doctors.name AS doctor_name', 'doctors.avatar AS doctor_avatar', 'doctors.title AS doctor_title', 'doctors.auth AS doctor_auth',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->leftJoin('doctors', 'doctors.id', '=', 'appointments.doctor_id')
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->where('appointments.id', $appointmentId)
            ->first();
    }
}
