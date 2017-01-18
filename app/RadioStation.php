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
    protected $fillable = [
        'title',
        'content',
        'img_url',
        'author',
        'd_or_p',
        'status',
        'valid'
    ];

    /**
     * 分页获取广播列表,并左连接获取广播已读状态表信息
     *
     * @param $user
     * @return mixed
     */
    public static function getRadioList($user)
    {
        return RadioStation::leftJoin('doctor_radio_read', function ($join) use ($user) {
            $join->on('radio_stations.id', '=', 'doctor_radio_read.radio_station_id')
                ->where('doctor_radio_read.user_id', '=', $user->id);
        })
//            ->where('status', 0) //1为过期
            ->where(function ($query) {
                $query->where('d_or_p', 'd')
                    ->orWhere('d_or_p', 'all');
            })
//            ->where('valid', '>', date('Y-m-d H:i:s')) //过期时间
            ->paginate(4);
    }

    /**
     * 获取未读广播数量
     *
     * @param $user
     * @return mixed
     */
    public static function getUnreadRadioCount($user)
    {
        return RadioStation::leftJoin('doctor_radio_read', function ($join) use ($user) {
            $join->on('radio_stations.id', '=', 'radio_read.radio_station_id')
                ->where('radio_read.user_id', '=', $user->id);
        })
//            ->where('status', 0) //1为过期
            ->where(function ($query) {
                $query->where('d_or_p', 'd')
                    ->orWhere('d_or_p', 'all');
            })
//            ->where('valid', '>', date('Y-m-d H:i:s')) //过期时间
            ->where('radio_read.value', 1)
            ->count();
    }
}
