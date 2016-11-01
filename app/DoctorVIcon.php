<?php
/**
 * Created by PhpStorm.
 * User: 乔小柒
 * Date: 2016/9/24
 * Time: 13:50
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorVIcon extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doctor_v_icon';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone'
    ];
}
