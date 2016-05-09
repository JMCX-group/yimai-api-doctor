<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DoctorContactRecord
 * @property  doctor_id
 * @property mixed contacts_id_list
 * @package App
 */
class DoctorContactRecord extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doctor_contact_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['doctor_id', 'contacts_id_list'];
}
