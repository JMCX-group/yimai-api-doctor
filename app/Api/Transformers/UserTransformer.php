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
        $this->idToName($user);

        return [
            'id' => $user['id'],
            'code' => $user['dp_code'],
            'phone' => $user['phone'],
            'name' => $user['name'],
            'sex' => $user['gender'],
            'province' => $user['province_id'],
            'city' => $user['city_id'],
            'hospital' => $user['hospital_id'],
            'department' => $user['dept_id'],
            'job_title' => $user['title'],
            'college' => $user['college_id'],
            'ID_number' => $user['id_num'],
            'tags' => $user['tag_id_list'],
            'personal_introduction' => $user['profile'],
            'inviter' => $user['inviter_dp_code']
        ];
    }

    /**
     * ID to id:name.
     *
     * @param $user
     * @return mixed
     */
    public function idToName($user)
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
            $user['dept_id'] = DeptStandard::find($user['dept_id']);
        }

        if (!empty($user['college_id'])) {
            $user['college_id'] = College::find($user['college_id']);
        }

        return $user;
    }
}
