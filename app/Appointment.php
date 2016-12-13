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
}
