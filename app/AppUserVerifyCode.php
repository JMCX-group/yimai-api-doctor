<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AppUserVerifyCode
 * @package App
 */
class AppUserVerifyCode extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_verify_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['phone', 'code'];
}
