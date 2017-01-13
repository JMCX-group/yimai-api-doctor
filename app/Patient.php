<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'password',
        'device_token',
        'name',
        'nickname',
        'avatar',
        'gender',
        'birthday',
        'province_id',
        'city_id',
        'tag_list',
        'blacklist',
        'my_doctors',
    ];
}
