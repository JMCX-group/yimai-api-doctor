<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\User;
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
//        dd($user);
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

    public function idToName($user)
    {
        //TODO: 需要把ID转换名称
    }
}
