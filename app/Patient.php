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
        'name',
        'nickname',
        'gender',
        'birthday',
        'province_id',
        'city_id',
        'tag_list',
        'my_doctors',
    ];
}
