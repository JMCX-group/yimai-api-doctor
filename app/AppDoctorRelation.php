<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppDoctorRelation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_doctor_relations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['app_doctor_id', 'app_doctor_friend_id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get my friends id list.
     * 
     * @param $myId
     * @return array
     */
    public static function getFriendIdList($myId)
    {
        $friendData = AppDoctorRelation::select('app_doctor_friend_id')
            ->where('app_doctor_id', $myId)
            ->get();

        $friend = array();
        foreach ($friendData as $data) {
            array_push($friend, $data->app_doctor_friend_id);
        }
        
        return $friend;
    }

    /**
     * Get my friends list.
     * 
     * @param $myId
     * @return mixed
     */
    public static function getFriends($myId)
    {
        return User::find(self::getFriendIdList($myId));
    }

    /**
     * Get my friend's friends id list.
     * 
     * @param $myFriendList
     * @param $myId
     * @return array
     */
    public static function getFriendsFriendsIdList($myFriendList, $myId)
    {
        $notSelectFriends = $myFriendList;
        array_push($notSelectFriends, $myId);
        
        $friendsFriendData = AppDoctorRelation::select('app_doctor_friend_id')
            ->whereIn('app_doctor_id', $myFriendList)
            ->whereNotIn('app_doctor_friend_id', $notSelectFriends)
            ->distinct()
            ->get();

        $friendsFriend = array();
        foreach ($friendsFriendData as $data) {
            array_push($friendsFriend, $data->app_doctor_friend_id);
        }
        
        return $friendsFriend;
    }

    /**
     * Get my friend's friends list.
     *
     * @param $myId
     * @return mixed
     */
    public static function getFriendsFriends($myId)
    {
        return User::find(self::getFriendsFriendsIdList(self::getFriendIdList($myId), $myId));
    }
}
