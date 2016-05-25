<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class DoctorRelation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doctor_relations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'doctor_friend_id',
        'friend_remarks',
        'doctor_read',
        'doctor_friend_read'
    ];

    /**
     * 获取和某个医生的共同好友的id list.
     * 
     * @param $myId
     * @param $id
     * @return mixed
     */
    public static function getCommonFriendIdList($myId, $id)
    {
        $relations = DoctorRelation::select('doctor_id', 'doctor_friend_id')
            ->where('doctor_friend_id', $myId)
            ->orWhere('doctor_friend_id', $id)
            ->get();

        $commonFriendIdList = array();
        foreach ($relations as $relation) {
            foreach ($relations as $relationItem) {
                if ($relation->doctor_id == $relationItem->doctor_id && $relation->doctor_friend_id != $relationItem->doctor_friend_id) {
                    array_push($commonFriendIdList, $relation->doctor_id);
                }
            }
        }
        
        return array_unique($commonFriendIdList);
    }

    /**
     * 获取和某人是否是好友关系,返回是2则互为好友.
     * 
     * @param $myId
     * @param $id
     * @return mixed
     */
    public static function getIsFriend($myId, $id)
    {
        return DB::select(
            "SELECT count(*) as count " .
            "FROM doctor_relations " .
            "WHERE (doctor_id=$myId AND doctor_friend_id=$id) OR (doctor_id=$id AND doctor_friend_id=$myId)"
        );
    }

    /**
     * Get my friends id list.
     *
     * @param $id
     * @return array
     */
    public static function getFriendIdList($id)
    {
        return DoctorRelation::where('doctor_id', $id)->lists('doctor_friend_id')->toArray();
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

        $friendsFriendData = DoctorRelation::select('doctor_friend_id')
            ->whereIn('doctor_id', $myFriendList)
            ->whereNotIn('doctor_friend_id', $notSelectFriends)
            ->distinct()
            ->get();

        $friendsFriend = array();
        foreach ($friendsFriendData as $data) {
            array_push($friendsFriend, $data->doctor_friend_id);
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

    /**
     * @param $friendIdList
     * @param $friendsFriendsIdList
     * @return array
     */
    public static function getCommonFriendsCount($friendIdList, $friendsFriendsIdList)
    {
        $retData = array();

        foreach ($friendsFriendsIdList as $item) {
            $count = DoctorRelation::where('doctor_id', $item)
                ->whereIn('doctor_friend_id', $friendIdList)
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
        $data = DoctorRelation::select('doctor_id', 'doctor_friend_id', 'doctor_read', 'doctor_friend_read', 'created_at')
            ->where('doctor_id', $id)
            ->orWhere('doctor_friend_id', $id)
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
                if ($value->doctor_id == $id && $value->doctor_id == $item->doctor_friend_id && $item->doctor_id == $value->doctor_friend_id) {
                    // 如果doctor id是自己,且有互为好友的关系
                    if ($value->doctor_read == 0) {
                        $unreadCount++;
                    }
                    $value['status'] = 'isFriend';
                    $value['word'] = '';
                    array_push($retData, $value);
                    $bool = false;
                    break;
                } elseif ($value->doctor_friend_id == $id && $value->doctor_id == $item->doctor_friend_id && $item->doctor_id == $value->doctor_friend_id) {
                    $bool = false;
                    break;
                }
            }

            if ($bool) {
                if ($value->doctor_id == $id) {
                    // 自己请求他人
                    if ($value->doctor_read == 0) {
                        $unreadCount++;
                    }
                    $value['status'] = 'waitForFriendAgree';
                    $value['word'] = '请求已发送';
                    array_push($retData, $value);
                } elseif ($value->doctor_friend_id == $id) {
                    // 他人请求自己
                    if ($value->doctor_friend_read == 0) {
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
     *
     * @param $id
     * @return array
     */
    public static function getNewFriends($id)
    {
        $list = self::getNewFriendsIdList($id)['id_list'];

        if (empty($list)) {
            return [];
        } else {
            $idList = array();
            foreach ($list as $item) {
                if ($item->doctor_id != $id) {
                    array_push($idList, $item->doctor_id);
                } elseif ($item->doctor_friend_id != $id) {
                    array_push($idList, $item->doctor_friend_id);
                }
            }

            $idListStr = implode(',', $idList);
            $users = DB::select(
                "select * from doctors where id in (" . $idListStr . ") order by find_in_set(id, '" . $idListStr . "')"
            );

            return [
                'users' => $users,
                'list' => $list
            ];
        }
    }

    /**
     * @param $id
     */
    public static function setReadStatus($id)
    {
        DoctorRelation::where('doctor_id', $id)->update(['doctor_read' => 1]);
        DoctorRelation::where('doctor_friend_id', $id)->update(['doctor_friend_read' => 1]);
    }

    /**
     * @param $myId
     * @param $friendId
     * @return mixed
     */
    public static function destroyRelation($myId, $friendId)
    {
        return DB::delete(
            "DELETE FROM doctor_relations " .
            "WHERE (doctor_id=$myId AND doctor_friend_id=$friendId) OR (doctor_id=$friendId AND doctor_friend_id=$myId)"
        );
    }
}
