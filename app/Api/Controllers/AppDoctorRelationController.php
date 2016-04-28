<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Transformers\Transformer;
use App\AppDoctorRelation;
use App\User;

class AppDoctorRelationController extends BaseController
{
    public function index()
    {

    }

    /**
     * Get relations.
     *
     * @return array|mixed
     */
    public function getRelations()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = Transformer::userListTransform(AppDoctorRelation::getFriends($user->id));

        return [
            'same' => User::getSameTypeContactCount($user->hospital_id, $user->dept_id, $user->college_id),
            'count' => [
                'doctor' => count($data['friends']),
                'hospital' => $data['hospital_count']
            ],
            'friends' => $data['friends']
        ];
    }

    /**
     * Get friends.
     *
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function getRelationsFriends()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = Transformer::userListTransform(AppDoctorRelation::getFriends($user->id));

        return [
            'count' => [
                'doctor' => count($data['friends']),
                'hospital' => $data['hospital_count']
            ],
            'friends' => $data['friends']
        ];
    }

    /**
     * Get friends friends.
     *
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function getRelationsFriendsFriends()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = Transformer::userListTransform(AppDoctorRelation::getFriendsFriends($user->id));

        return [
            'count' => [
                'doctor' => count($data['friends']),
                'hospital' => $data['hospital_count']
            ],
            'friends' => $data['friends']
        ];
    }
}
