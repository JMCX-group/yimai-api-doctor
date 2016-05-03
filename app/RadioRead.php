<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RadioRead extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'radio_read';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'radio_station_id', 'value'];
}
