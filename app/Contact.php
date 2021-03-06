<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'phone',
        'name',
        'status'
    ];
    
    /**
     * @var bool
     */
    public $timestamps = false;
}
