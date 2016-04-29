<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

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
     * Get my friends id list.
     *
     * @param $id
     * @return array
     */
    public static function getFriendIdList($id)
    {
        $friendData = AppDoctorRelation::select('app_doctor_friend_id')
            ->where('app_doctor_id', $id)
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
     * @param $id
     * @return mixed
     */
    public static function getFriends($id)
    {
        return User::find(self::getFriendIdList($id));
    }

    /**
     * Get my friend's friends id list.
     *
     * @param $myFriendList
     * @param $id
     * @return array
     */
    public static function getFriendsFriendsIdList($myFriendList, $id)
    {
        $notSelectFriends = $myFriendList;
        array_push($notSelectFriends, $id);

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
     * Get my friend's friends list and common friends count.
     *
     * @param $id
     * @return mixed
     */
    public static function getFriendsFriends($id)
    {
        $friendIdList = self::getFriendIdList($id);
        $friendsFriendsIdList = self::getFriendsFriendsIdList($friendIdList, $id);

        return [
            'user' => User::find($friendsFriendsIdList),
            'count' => self::getCommonFriendsCount($friendIdList, $friendsFriendsIdList)
        ];
    }

    public static function getCommonFriendsCount($friendIdList, $friendsFriendsIdList)
    {
        $retData = array();

        foreach ($friendsFriendsIdList as $item) {
            $count = AppDoctorRelation::where('app_doctor_id', $item)
                ->whereIn('app_doctor_friend_id', $friendIdList)
                ->get()
                ->count();

            $retData[$item] = $count;
        }

        return $retData;
    }

    /**
     * Get new friend id list.
     *
     * @param $id
     * @return array
     */
    public static function getNewFriendsIdList($id)
    {
        $data = AppDoctorRelation::select('app_doctor_id', 'app_doctor_friend_id', 'read', 'created_at')
            ->where('app_doctor_id', $id)
            ->orWhere('app_doctor_friend_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return self::groupByNewFriends($data, $id);
    }

    /**
     * Group by new friends all info.
     * Fill status and word.
     *
     * @param $data
     * @param $id
     * @return array
     */
    private static function groupByNewFriends($data, $id)
    {
        $retData = array();
        $unreadCount = 0;

        foreach ($data as &$value) {
            $bool = true;

            foreach ($data as $item) {
                if ($value->app_doctor_id == $id && $value->app_doctor_id == $item->app_doctor_friend_id && $item->app_doctor_id == $value->app_doctor_friend_id) {
                    if ($value->read == 0) {
                        $unreadCount++;
                    }
                    $value['status'] = 'isFriend';
                    $value['word'] = '';
                    array_push($retData, $value);
                    $bool = false;
                    break;
                } elseif ($value->app_doctor_friend_id == $id && $value->app_doctor_id == $item->app_doctor_friend_id && $item->app_doctor_id == $value->app_doctor_friend_id) {
                    $bool = false;
                    break;
                }
            }

            if ($bool) {
                if ($value->app_doctor_id == $id) {
                    if ($value->read == 0) {
                        $unreadCount++;
                    }
                    $value['status'] = 'waitForFriendAgree';
                    $value['word'] = '请求已发送';
                    array_push($retData, $value);
                } elseif ($value->app_doctor_friend_id == $id) {
                    if ($value->read == 0) {
                        $unreadCount++;
                    }
                    $value['status'] = 'waitForSure';
                    $value['word'] = '请求添加您';
                    array_push($retData, $value);
                }
            }
        }

        return [
            'unread' => $unreadCount,
            'id_list' => $retData
        ];
    }

    /**
     * Get new friends all info.
     * @param $id
     * @return array
     */
    public static function getNewFriends($id)
    {
        $list = self::getNewFriendsIdList($id)['id_list'];

        $idList = array();
        foreach ($list as $item) {
            if ($item->app_doctor_id != $id) {
                array_push($idList, $item->app_doctor_id);
            } elseif ($item->app_doctor_friend_id != $id) {
                array_push($idList, $item->app_doctor_friend_id);
            }
        }

        $idListStr = implode(',', $idList);
        $users = DB::select(
            "select * from app_doctors where id in (" . $idListStr . ") order by find_in_set(id, '" . $idListStr . "')"
        );

        return [
            'users' => $users,
            'list' => $list
        ];
    }
}
