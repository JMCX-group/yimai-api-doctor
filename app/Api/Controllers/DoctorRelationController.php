<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Requests\RelationIdRequest;
use App\Api\Requests\RemarksRequest;
use App\Api\Transformers\Transformer;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorRelationController extends BaseController
{
    public function index()
    {

    }

    /**
     * 新增好友关系
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|mixed
     */
    public function store(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        if ($request['id'] == $user->id) {
            return response()->json(['message' => '不可以添加自己'], 400);
        }

        /**
         * 可以通过ID、电话、医脉码来添加好友。
         */
        if (isset($request['id']) && !empty($request['id'])) {
            $friend = User::find($request['id']);
            if (!Empty($friend)) {
                $data['doctor_friend_id'] = $request['id'];
            }
        } else {
            if (isset($request['phone']) && !empty($request['phone'])) {
                $friend = User::where('phone', $request['phone'])->first();
                if (!Empty($friend)) {
                    $data['doctor_friend_id'] = $friend['id'];
                }
            } else {
                if (isset($request['code']) && !empty($request['code'])) {
                    $deptId = substr($request['code'], 0, 3);
                    $dpCode = substr($request['code'], 3);
                    $friend = User::where('dp_code', $dpCode)->where('dept_id', $deptId)->first();
                    if (!Empty($friend)) {
                        $data['doctor_friend_id'] = $friend['id'];
                    }
                }
            }
        }

        if (isset($data)) {
            $data['doctor_id'] = $user->id;
            $data['doctor_read'] = 1;
            $data['doctor_friend_read'] = 0;

            try {
                if (DoctorRelation::create($data)) {
//                    return $this->response->noContent();
                    return response()->json(['success' => ''], 204); //给肠媳适配。。
                } else {
                    return response()->json(['message' => '已添加过'], 500);
                }
            } catch (\Exception $e) {
                Log::info('add friend', ['context' => $e->getMessage()]);
                return response()->json(['message' => '添加失败'], 400);
            }
        } else {
            return response()->json(['message' => '该好友未加入医脉'], 400);
        }
    }

    /**
     * 被申请一方确认关系
     *
     * @param RelationIdRequest $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|mixed
     */
    public function update(RelationIdRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $relation = DoctorRelation::where('doctor_id', $request['id'])->where('doctor_friend_id', $user->id)->first();
        if (!Empty($relation)) {
            if ($relation->where('doctor_id', $request['id'])
                ->where('doctor_friend_id', $user->id)
                ->update(['doctor_friend_read' => 1])
            ) {
                $data = [
                    'doctor_id' => $user->id,
                    'doctor_friend_id' => $request['id'],
                    'doctor_read' => 1,
                    'doctor_friend_read' => 0
                ];

                try {
                    if (DoctorRelation::create($data)) {
//                        return $this->response->noContent();
                        return response()->json(['success' => ''], 204); //给肠媳适配。。
                    } else {
                        return response()->json(['message' => '添加失败'], 500);
                    }
                } catch (\Exception $e) {
                    Log::info('add friend', ['context' => $e->getMessage()]);
                    return response()->json(['message' => '添加失败'], 400);
                }
            } else {
                return response()->json(['message' => '确认失败'], 500);
            }
        } else {
            return response()->json(['message' => '关系不存在'], 400);
        }
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
     * Get common friends.
     * 
     * @param $friendId
     * @return array|mixed
     */
    public function getCommonFriends($friendId)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $friendsIdList = DoctorRelation::getFriendIdList($user->id);
        $commonFriendsIdList = DoctorRelation::where('doctor_id', $friendId)
            ->whereIn('doctor_friend_id', $friendsIdList)
            ->lists('doctor_friend_id')
            ->toArray();
        $commonFriends = User::find($commonFriendsIdList);
        
        return Transformer::usersTransform($commonFriends);
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
//            return $this->response->noContent();
            return response()->json(['success' => ''], 204); //给肠媳适配。。
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

//        return $this->response->noContent();
        return response()->json(['success' => ''], 204); //给肠媳适配。。
    }

    /**
     * @param RemarksRequest $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|mixed
     */
    public function setRemarks(RemarksRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        try {
            if (DoctorRelation::where('doctor_id', $user->id)
                ->where('doctor_friend_id', $request['friend_id'])
                ->update(['friend_remarks' => $request['remarks']])
            ) {
//                return $this->response->noContent();
                return response()->json(['success' => ''], 204); //给肠媳适配。。
            } else {
                return response()->json(['message' => '备注失败'], 500);
            }
        } catch (\Exception $e) {
            Log::info('set friend remarks', ['context' => $e->getMessage()]);
            return response()->json(['message' => '备注失败'], 400);
        }
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|mixed
     */
    public function destroy(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        try {
            if (DoctorRelation::destroyRelation($user->id, $request['friend_id'])
            ) {
//                return $this->response->noContent();
                return response()->json(['success' => ''], 204); //给肠媳适配。。
            } else {
                return response()->json(['message' => '删除失败'], 500);
            }
        } catch (\Exception $e) {
            Log::info('del friend', ['context' => $e->getMessage()]);
            return response()->json(['message' => '删除失败'], 400);
        }
    }
}
