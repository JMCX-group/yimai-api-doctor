<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientWallet extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patient_wallets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'order_count',
        'total',
        'freeze'
    ];
}
