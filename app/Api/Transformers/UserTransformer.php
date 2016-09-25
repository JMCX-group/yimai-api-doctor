<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\College;
use App\DeptStandard;
use App\Hospital;
use App\User;
use App\Province;
use App\City;
use League\Fractal\TransformerAbstract;

/**
 * Class UserTransformer
 * @package App\Api\Transformers
 */
class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        // ID convert id:name
        self::idToName($user);

        return [
            'id' => $user['id'],
            'code' => $user['dp_code'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'rong_yun_token' => $user['rong_yun_token'],
            'name' => $user['name'],
            'head_url' => ($user['avatar'] == '') ? null : $user['avatar'],
            'sex' => $user['gender'],
            'province' => $user['province_id'],
            'city' => $user['city_id'],
            'hospital' => $user['hospital_id'],
            'department' => $user['dept_id'],
            'job_title' => $user['title'],
            'college' => $user['college_id'],
            'ID_number' => $user['id_num'],
            'tags' => $user['tag_list'],
            'personal_introduction' => $user['profile'],
            'is_auth' => $user['auth'],
            'auth_img' => $user['auth_img'],
            'fee_switch' => $user['fee_switch'],
            'fee' => $user['fee'],
            'fee_face_to_face' => $user['fee_face_to_face'],
            'admission_set_fixed' => $user['admission_set_fixed'],
            'admission_set_flexible' => self::delOutdated(json_decode($user['admission_set_flexible'], true)),
            'verify_switch' => $user['verify_switch'],
            'friends_friends_appointment_switch' => $user['friends_friends_appointment_switch'],
            'application_card' => $user['application_card'],
            'address' => $user['address'],
            'inviter' => $user['inviter_dp_code']
        ];
    }

    /**
     * 删除过期时间
     *
     * @param $data
     * @return string
     */
    public static function delOutdated($data)
    {
        if ($data == '' || $data == null) {
            return null;
        }

        $now = time();
        $newData = array();
        foreach ($data as $item) {
            if (strtotime($item['date']) > $now) {
                array_push($newData, $item);
            }
        }

        return json_encode($newData);
    }

    /**
     * ID to id:name.
     *
     * @param $user
     * @return mixed
     */
    public static function idToName($user)
    {
        if (!empty($user['province_id'])) {
            $user['province_id'] = Province::find($user['province_id']);
        }

        if (!empty($user['city_id'])) {
            $user['city_id'] = City::select('id', 'name')->find($user['city_id']);
        }

        if (!empty($user['hospital_id'])) {
            $user['hospital_id'] = Hospital::select('id', 'name')->find($user['hospital_id']);
        }

        if (!empty($user['dept_id'])) {
            $user['dept_id'] = DeptStandard::select('id', 'name')->find($user['dept_id']);
        }

        if (!empty($user['college_id'])) {
            $user['college_id'] = College::select('id', 'name')->find($user['college_id']);
        }

        // Spell dp code.
        if (!empty($user['dp_code'])) {
            $user['dp_code'] = User::getDpCode($user['id']);
        }

        return $user;
    }

    /**
     * @param $user
     * @return array
     */
    public static function transformUser($user)
    {
        // ID convert id:name
        self::idToName($user);

        return [
            'id' => $user['id'],
            'code' => $user['dp_code'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'rong_yun_token' => $user['rong_yun_token'],
            'name' => $user['name'],
            'head_url' => ($user->avatar == '') ? null : $user->avatar,
            'sex' => $user['gender'],
            'province' => $user['province_id'],
            'city' => $user['city_id'],
            'hospital' => $user['hospital_id'],
            'department' => $user['dept_id'],
            'job_title' => $user['title'],
            'college' => $user['college_id'],
            'ID_number' => $user['id_num'],
            'tags' => $user['tag_list'],
            'personal_introduction' => $user['profile'],
            'is_auth' => $user['auth'],
            'auth_img' => $user['auth_img'],
            'fee_switch' => $user['fee_switch'],
            'fee' => $user['fee'],
            'fee_face_to_face' => $user['fee_face_to_face'],
            'admission_set_fixed' => $user['admission_set_fixed'],
            'admission_set_flexible' => self::delOutdated(json_decode($user['admission_set_flexible'], true)),
            'verify_switch' => $user['verify_switch'],
            'friends_friends_appointment_switch' => $user['friends_friends_appointment_switch'],
            'application_card' => $user['application_card'],
            'address' => $user['address'],
            'inviter' => $user['inviter_dp_code']
        ];
    }
}
