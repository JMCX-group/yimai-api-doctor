<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class AppointmentFee extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'appointment_fees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'locums_id',
        'appointment_id',
        'total_fee',
        'reception_fee',
        'platform_fee',
        'intermediary_fee',
        'guide_fee',
        'default_fee_rate',
        'status',
        'time_expire',
        'settlement_status'
    ];

    /**
     * 各种状态的总费用
     * 资金状态：paid（已支付）、completed（已完成）、cancelled（已取消）
     *
     * @param $patientId
     * @param string $status
     * @return mixed
     */
    public static function getTotalFees($patientId, $status = 'completed')
    {
        return DB::select("
            SELECT SUM(`total_fee`) AS fee
            FROM `appointment_fees` 
            WHERE patient_id=$patientId AND `status`='$status';
        ");
    }

    /**
     * 总罚款
     *
     * @param $patientId
     * @return mixed
     */
    public static function getDefaultFees($patientId)
    {
        return DB::select("
            SELECT SUM(`total_fee`) AS fee
            FROM `appointment_fees` 
            WHERE patient_id=$patientId AND `status`='cancelled';
        ");
    }

    /**
     * 总冻结费用
     *
     * @param $patientId
     * @return mixed
     */
    public static function getFreezeFees($patientId)
    {
        return self::getTotalFees($patientId, 'paid');
    }

    /**
     * 查询所有进入代缴税列表的id list，用于更新状态
     *
     * @param $doctorId
     * @param $year
     * @param $month
     * @return mixed
     */
    public static function allPending($doctorId, $year, $month)
    {
        return DB::select("
            SELECT `id`
            FROM `appointment_fees` 
            WHERE doctor_id=$doctorId AND status='completed' AND date_format(`updated_at`, '%Y')=$year AND date_format(`updated_at`, '%m')=$month;
        ");
    }


    /**
     * 查询总额
     *
     * @param $userId
     * @return mixed
     */
    public static function totalFeeSum($userId)
    {
        return DB::select("
            SELECT SUM(`reception_fee`) AS sum_value 
            FROM `appointment_fees` 
            WHERE `doctor_id`='$userId' AND `status`!='paid' 
        ");
    }

    /**
     * 查询可提现/待结算/已提现总额
     *
     * @param $userId
     * @param $status
     * @return mixed
     */
    public static function selectSum($userId, $status)
    {
        return DB::select("
            SELECT SUM(`reception_fee`) AS sum_value 
            FROM `appointment_fees` 
            WHERE `doctor_id`=$userId AND `settlement_status`='$status' AND `status`!='paid' 
        ");
    }

    /**
     * 按月查询某医生当月收入总额
     *
     * @param $doctorId
     * @return mixed
     */
    public static function sumTotal($doctorId)
    {
        return DB::select("
            SELECT 
                date_format(`time_expire`, '%Y') AS 'year',
                date_format(`time_expire`, '%m') AS 'month',
                sum(`reception_fee`) AS total 
            FROM `appointment_fees` 
            WHERE `doctor_id`=$doctorId AND `status`!='paid' 
            GROUP BY date_format(`time_expire`, '%Y-%m');
        ");
    }
}
