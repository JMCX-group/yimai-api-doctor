<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FaceToFaceAdvice extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'face_to_face_advices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'doctor_id',
        'phone',
        'name',
        'price',
        'transaction_id',
        'status'
    ];
}
