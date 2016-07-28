<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorAddressBook extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doctor_address_book';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'content'];
}
