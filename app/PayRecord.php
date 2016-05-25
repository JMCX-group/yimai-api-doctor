<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayRecord extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pay_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id',
        'out_trade_no',
        'price',
        'open_id',
        'status',
        'wx_pay_status_code'
    ];
}
