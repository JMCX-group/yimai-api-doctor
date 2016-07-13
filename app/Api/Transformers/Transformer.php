<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\DeptStandard;
use App\Hospital;

class Transformer
{
    /**
     * Transform user list.
     *
     * @param $users
     * @return array
     */
    public static function userListTransform($users)
    {
        $hospitalIdList = array();
        $deptIdList = array();
        $newUsers = array();

        foreach ($users as $user) {
            array_push($hospitalIdList, $user->hospital_id);
            array_push($deptIdList, $user->dept_id);

            array_push($newUsers, self::userTransform($user));
        }

        return [
            'friends' => self::idToName($newUsers, $hospitalIdList, $deptIdList),
            'hospital_count' => count(array_unique($hospitalIdList))
        ];
    }

    /**
     * Transform users.
     * @param $users
     * @return mixed
     */
    public static function usersTransform($users)
    {
        $hospitalIdList = array();
        $deptIdList = array();
        $newUsers = array();

        foreach ($users as $user) {
            array_push($hospitalIdList, $user->hospital_id);
            array_push($deptIdList, $user->dept_id);

            array_push($newUsers, self::userTransform($user));
        }

        return self::idToIdName($newUsers, $hospitalIdList, $deptIdList);
    }

    /**
     * Transform user.
     *
     * @param $user
     * @return array
     */
    public static function userTransform($user)
    {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'head_url' => ($user->avatar == '') ? null : $user->avatar,
            'hospital' => $user['hospital_id'],
            'department' => $user['dept_id'],
            'job_title' => $user['title']
        ];
    }

    /**
     * Transform contacts.
     *
     * @param $user
     * @return array
     */
    public static function contactsTransform($user)
    {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'head_url' => ($user->avatar == '') ? null : $user->avatar,
            'department' => $user['dept_id'],
            'is_auth' => $user['auth']
        ];
    }

    /**
     * @param $user
     * @return array
     */
    public static function findDoctorTransform($user)
    {
        return [
            'user' => [
                'is_friend' => $user->is_friend,

                'id' => $user->id,
                'code' => $user->dp_code,
                'name' => $user->name,
                'head_url' => ($user->avatar == '') ? null : $user->avatar,
                'job_title' => $user->title,
                'province' => $user->province,
                'city' => $user->city,
                'hospital' => $user->hospital,
                'department' => $user->dept,
                'college' => $user->college,
                'tags' => $user->tag_list,
                'personal_introduction' => $user->profile,
                'is_auth' => $user->auth,
                'common_friend_list' => $user->common_friend_list,
            ]
        ];
    }

    /**
     * @param $user
     * @param $relation
     * @return array
     */
    public static function searchDoctorTransform($user, $relation = null)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'head_url' => ($user->avatar == '') ? null : $user->avatar,
            'job_title' => $user->title,
            'city' => $user->city,
            'hospital' => [
                'id' => $user->hospital_id,
                'name' => $user->hospital,
            ],
            'department' => [
                'id' => $user->dept_id,
                'name' => $user->dept,
            ],
            'relation' => $relation
        ];
    }

    /**
     * Id to name.
     *
     * @param $users
     * @param $hospitalIdList
     * @param $deptIdList
     * @return mixed
     */
    public static function idToName($users, $hospitalIdList, $deptIdList)
    {
        $hospitals = Hospital::select('id', 'name')->find($hospitalIdList);
        $depts = DeptStandard::select('id', 'name')->find($deptIdList);

        foreach ($users as &$user) {
            foreach ($hospitals as $hospital) {
                if ($user['hospital'] == $hospital['id']) {
                    $user['hospital'] = $hospital['name'];
                }
            }

            foreach ($depts as $dept) {
                if ($user['department'] == $dept['id']) {
                    $user['department'] = $dept['name'];
                }
            }
        }

        return $users;
    }

    /**
     * ID to ID:Name.
     *
     * @param $users
     * @param $hospitalIdList
     * @param $deptIdList
     * @return mixed
     */
    public static function idToIdName($users, $hospitalIdList, $deptIdList)
    {
        $hospitals = Hospital::select('id', 'name')->find($hospitalIdList);
        $depts = DeptStandard::select('id', 'name')->find($deptIdList);

        foreach ($users as &$user) {
            foreach ($hospitals as $hospital) {
                if ($user['hospital'] == $hospital['id']) {
                    $user['hospital'] = [
                        'id' => $hospital['id'],
                        'name' => $hospital['name'],
                    ];
                }
            }

            foreach ($depts as $dept) {
                if ($user['department'] == $dept['id']) {
                    $user['department'] = [
                        'id' => $dept['id'],
                        'name' => $dept['name'],
                    ];
                }
            }
        }

        return $users;
    }

    /**
     * @param $id
     * @param $users
     * @param $list
     * @return mixed
     */
    public static function newFriendTransform($id, $users, $list)
    {
        $retData = array();
        $hospitalIdList = array();
        $deptIdList = array();

        foreach ($users as $user) {
            foreach ($list as $item) {
                if ($user->id == $item->doctor_id || $user->id == $item->doctor_friend_id) {
                    array_push(
                        $retData,
                        [
                            'id' => $user->id,
                            'name' => $user->name,
                            'head_url' => ($user->avatar == '') ? null : $user->avatar,
                            'hospital' => $user->hospital_id,
                            'department' => $user->dept_id,
                            'unread' => ($id == $item->doctor_id) ? $item->doctor_read : $item->doctor_friend_read,
                            'status' => $item->status,
                            'word' => $item->word,
                        ]
                    );
                }
            }

            array_push($hospitalIdList, $user->hospital_id);
            array_push($deptIdList, $user->dept_id);
        };

        return self::idToName(
            $retData,
            array_unique(array_values($hospitalIdList)),
            array_unique(array_values($deptIdList))
        );
    }

    /**
     * Transform friends friends.
     * 按共同好友数量倒序.
     *
     * @param $friends
     * @param $count
     * @return mixed
     */
    public static function friendsFriendsTransform($friends, $count)
    {
        foreach ($friends as &$friend) {
            $friend['common_friend_count'] = $count[$friend['id']];
        }

        usort($friends, function ($a, $b) {
            $al = $a['common_friend_count'];
            $bl = $b['common_friend_count'];
            if ($al == $bl)
                return 0;
            return ($al > $bl) ? -1 : 1;
        });

        return $friends;
    }

    /**
     * 格式化约诊详细信息
     *
     * @param $appointments
     * @param $doctor
     * @return array
     */
    public static function appointmentsTransform($appointments, $doctor)
    {
        return [
            'doctor_info' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'head_url' => ($doctor->avatar == '') ? null : $doctor->avatar,
                'job_title' => $doctor->title,
                'hospital' => $doctor->hospital,
                'department' => $doctor->dept
            ],
            'patient_info' => [
                'name' => $appointments->patient_name,
                'head_url' => ($appointments->patient_avatar == '') ? null : $appointments->patient_avatar,
                'sex' => $appointments->patient_gender,
                'age' => $appointments->patient_age,
                'phone' => $appointments->patient_phone,
                'history' => $appointments->patient_history,
                'img_url' => $appointments->patient_imgs
            ],
            'other_info' => [
                'progress' => $appointments->progress,
                'time_line' => $appointments->time_line
            ]
        ];
    }
}
