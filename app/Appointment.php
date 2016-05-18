<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
        'patient_name',
        'patient_phone',
        'patient_gender',
        'patient_age',
        'patient_history',
        'patient_imgs',
        'doctor_id',
        'visit_time',
        'am_pm',
        'status'
    ];
}
