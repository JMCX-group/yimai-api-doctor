<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'out_trade_no',
        'total_fee',
        'body',
        'detail',
        'type',
        'time_start',
        'time_expire',
        'ret_data',
        'status',
        'settlement_status'
    ];

    /**
     * 查询总额
     *
     * @param $userId
     * @return mixed
     */
    public static function totalFeeSum($userId)
    {
        return DB::select("SELECT SUM(`total_fee`) as sum_value FROM orders WHERE doctor_id=" . $userId);
    }

    /**
     * 查询可提现总额
     *
     * @param $userId
     * @return mixed
     */
    public static function billableSum($userId)
    {
        return DB::select("SELECT SUM(`total_fee`) as sum_value FROM orders WHERE doctor_id=" . $userId . " AND `settlement_status`='可提现'");
    }

    /**
     * 查询待结算总额
     *
     * @param $userId
     * @return mixed
     */
    public static function pendingSum($userId)
    {
        return DB::select("SELECT SUM(`total_fee`) as sum_value FROM orders WHERE doctor_id=" . $userId . " AND `settlement_status`='待结算'");
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
                sum(total_fee) AS total 
            FROM orders 
            WHERE doctor_id=$doctorId 
            GROUP BY date_format(`time_expire`, '%Y-%m');
        ");
    }
}
