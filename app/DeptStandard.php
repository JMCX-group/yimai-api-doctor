<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeptStandard extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "dept_standards";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'name'];
}
