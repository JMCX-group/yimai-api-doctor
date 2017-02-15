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
        'code',
        'tag_list',
        'blacklist',
        'my_doctors',
    ];

    /**
     * Generate new health consultant code.
     * 长度7位：城市编号4位 + 000开始的3位编码.
     *
     * @param $cityId
     * @return mixed
     */
    public static function generateHealthConsultantCode($cityId)
    {
        $cityCode = City::find($cityId)->code;
        $code = self::basicHealthConsultantCode($cityId);

        return str_pad($cityCode, 4, '0', STR_PAD_LEFT) . str_pad($code, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get basic code.
     * 000开始的3位编码.
     *
     * @param $cityId
     * @return mixed
     */
    public static function basicHealthConsultantCode($cityId)
    {
        $data = Patient::select('code')
            ->where('city_id', $cityId)
            ->orderBy('code', 'desc')
            ->first();

        if (isset($data->code)) {
            $code = intval($data->code) + 1;
        } else {
            $code = 1;
        }

        return $code;
    }

    /**
     * Get health consultant code.
     * 长度7位：城市编号4位 + 000开始的3位编码.
     *
     * @param $cityId
     * @param $code
     * @return string
     */
    public static function getHealthConsultantCode($cityId, $code)
    {
        $cityCode = City::find($cityId)->code;

        return str_pad($cityCode, 4, '0', STR_PAD_LEFT) . str_pad($code, 3, '0', STR_PAD_LEFT);
    }
}
