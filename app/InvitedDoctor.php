<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvitedDoctor extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invited_doctors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'status', //processing：认证中；completed：完成认证
        'bonus'
    ];
}
