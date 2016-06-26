<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdmissionsMsg extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'admissions_msg';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'appointment_id',
        'locums_id',
        'locums_name',
        'patient_name',
        'doctor_id',
        'doctor_name',
        'type',
        'status',
        'read_status'
    ];
}
