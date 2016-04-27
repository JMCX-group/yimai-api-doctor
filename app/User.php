<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * Class User
 * @package App
 */
class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_doctors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['phone', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Generate new DP Code.
     *
     * @param $cityId
     * @param $deptId
     * @return mixed
     */
    public static function generateDpCode($cityId, $deptId)
    {
        $data = User::select('dp_code')
            ->where('city_id', $cityId)
            ->where('dept_id', $deptId)
            ->get();

        return intval($data->first()->dp_code) + 1;
    }

    /**
     * Get DP Code.
     *
     * @param $id
     * @return string
     */
    public static function getDpCode($id)
    {
        $data = User::select('app_doctors.id', 'app_doctors.dp_code', 'app_doctors.name', 'app_doctors.dept_id', 'citys.code')
            ->where('app_doctors.id', $id)
            ->join('citys', 'app_doctors.city_id', '=', 'citys.id')
            ->get()
            ->first();

        $dpCode = $data->code . str_pad($data->dept_id, 3, '0', STR_PAD_LEFT) . $data->dp_code;

        return $dpCode;
    }

    /**
     * Get inviter name.
     * 
     * @param $dpCode
     * @return bool
     */
    public static function getInviter($dpCode)
    {
        $data = User::select('name')
            ->where('city_id', City::select('id')->where('code', substr($dpCode, 0, 3))->get()->first()->id)
            ->where('dept_id', substr($dpCode, 3, 3))
            ->where('dp_code', substr($dpCode, 6))
            ->get();

        if (isset($data->first()->name)) {
            return $data->first()->name;
        } else {
            return false;
        }
    }
}
