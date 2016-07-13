<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RadioStation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'radio_stations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'content', 'img_url', 'author', 'status', 'valid'];
}
