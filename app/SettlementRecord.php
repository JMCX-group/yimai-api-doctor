<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SettlementRecord extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'settlement_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'total',
        'tax_payment',
        'year',
        'month',
        'status', //结算状态； 0：未缴税；1：已完成结算，可提现
        'withdraw_status', //提现状态，是否已提现；0为未提现，1为申请提现，9为成功
        'withdraw_request_date',
        'withdraw_confirm_date'
    ];
}
