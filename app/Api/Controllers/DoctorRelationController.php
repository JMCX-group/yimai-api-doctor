<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Transformers\Transformer;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\User;
use Illuminate\Http\Request;

class DoctorRelationController extends BaseController
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

        $data = Transformer::userListTransform(DoctorRelation::getFriends($user->id));

        return [
            'same' => User::getSameTypeContactCount($user->hospital_id, $user->dept_id, $user->college_id),
            'unread' => DoctorRelation::getNewFriendsIdList($user->id)['unread'],
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

        $data = Transformer::userListTransform(DoctorRelation::getFriends($user->id));

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

        $friendsFriendsInfo = DoctorRelation::getFriendsFriends($user->id);
        $data = Transformer::userListTransform($friendsFriendsInfo['user']);

        return [
            'count' => [
                'doctor' => count($data['friends']),
                'hospital' => $data['hospital_count']
            ],
            'friends' => Transformer::friendsFriendsTransform($data['friends'], $friendsFriendsInfo['count'])
        ];
    }

    /**
     * Get new friends info.
     * Set read status.
     *
     * @return array|mixed
     */
    public function getNewFriends()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = DoctorRelation::getNewFriends($user->id);
        if (empty($data)) {
            return $this->response->noContent();
        } else {
            DoctorRelation::setReadStatus($user->id);

            return ['friends' => Transformer::newFriendTransform($user->id, $data['users'], $data['list'])];
        }
    }

    /**
     * 同步前台管理的最近联系人记录
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function pushRecentContacts(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $contactRecord = DoctorContactRecord::where('doctor_id', $user->id)->get();

        if (count($contactRecord) == 0) {
            $contactRecord = new DoctorContactRecord();
            $contactRecord->doctor_id = $user->id;
            $contactRecord->contacts_id_list = $request['id_list'];
            $contactRecord->save();
        } else {
            DoctorContactRecord::where('doctor_id', $user->id)
                ->update(['contacts_id_list' => $request['id_list']]);
        }

        return $this->response->noContent();
    }
}
