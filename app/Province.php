<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Province
 * @package App
 */
class Province extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'provinces';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
