<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Hospital
 * @package App
 */
class Hospital extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['city', 'name', 'three_a', 'top_dept_num', 'status'];

    /**
     * @var string
     */
    protected $table = 'hospitals';
}
