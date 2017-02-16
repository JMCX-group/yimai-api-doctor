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
        'status', //wait：等待邀请；invited：已邀请/未加入；re-invite：可以重新邀请了；join：已加入；processing：认证中；completed：完成认证
        'bonus'
    ];
}
