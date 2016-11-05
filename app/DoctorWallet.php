<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorWallet extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doctor_wallets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'order_count',
        'total',
        'pending',
        'refunded'
    ];
}
