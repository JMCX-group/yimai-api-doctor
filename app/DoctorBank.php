<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorBank extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doctor_banks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'bank_name',
        'bank_info',
        'bank_no',
        'real_name_verify',
        'status',
        'desc'
    ];
}
