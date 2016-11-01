<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Helper\Sms;
use App\Api\Requests\AddressRequest;
use App\Api\Requests\RelationIdRequest;
use App\Api\Requests\RemarksRequest;
use App\Api\Transformers\Transformer;
use App\DoctorAddressBook;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\DoctorVIcon;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Api\Helper\GetDoctor;

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
     * 添加所有通讯录里的所有医生。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function addAll(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $idList = $request['id_list'];
        $idArr = explode(',', $idList);
        $confirmedIdArr = User::whereIn('id', $idArr)->lists('id')->toArray();

        foreach ($confirmedIdArr as $item) {
            $data['doctor_id'] = $user->id;
            $data['doctor_friend_id'] = $item;
            $data['doctor_read'] = 1;
            $data['doctor_friend_read'] = 0;

            try {
                DoctorRelation::create($data);
            } catch (\Exception $e) {
                Log::info('add friend', ['context' => $e->getMessage()]);
                continue;
            }
        }

        //发送短信:
        $this->sendInviteAllFriends($user->id, $user->name);

        return response()->json(['success' => ''], 204);
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

    /**
     * 上传通讯录
     *
     * @param AddressRequest $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function uploadAddressBook(AddressRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $addressBook = DoctorAddressBook::find($user->id);
        if (!isset($addressBook->id)) {
            $addressBook = new DoctorAddressBook();
            $addressBook->id = $user->id;
        }

        $addressBook->content = $request->get('content'); //直接json入库
        $addressBook->save();

        $data = $this->contactsAnalysis($user->id, json_decode($addressBook['content'], true));

        return response()->json(compact('data'), 200);
    }

    /**
     * 分析通讯录的数据
     *
     * @param $userId
     * @param $content
     * @return array
     */
    public function contactsAnalysis($userId, $content)
    {
        //获取好友列表:
        $friendsIdList = DoctorRelation::getFriendIdList($userId);
        $friends = User::whereIn('id', $friendsIdList)->get();

        //获取电话列表:
        $phoneArr = array();
        foreach ($content as $item) {
            array_push($phoneArr, $item['phone']);
        }
        $allFriends = User::whereIn('phone', $phoneArr)->get();

        //找到通讯录中已在医脉加过的好友:
        $inYM_addFriendPhoneList = array();
        foreach ($friends as $friend) {
            if (in_array($friend['phone'], $phoneArr)) {
                array_push($inYM_addFriendPhoneList, $friend['phone']);
            }
        }

        //找到在医脉中加了好友和没加好友:
        $inYM_addFriends = array();
        $inYM_notAddFriends = array();
        $inYM_phoneList = array();
        foreach ($allFriends as $allFriend) {
            if (in_array($allFriend['phone'], $inYM_addFriendPhoneList)) {
                $allFriend->is_add_friend = '1';
                array_push($inYM_addFriends, $allFriend);
            } else {
                $allFriend->is_add_friend = '0';
                array_push($inYM_notAddFriends, $allFriend);
            }
            array_push($inYM_phoneList, $allFriend['phone']);
        }
        $inYM = array_merge($inYM_addFriends, $inYM_notAddFriends);

        //排除已加过和已加入医脉的好友,获得"其他":
        $others = array();
        $otherPhoneList = array();
        foreach ($content as $item) {
            if (!in_array($item['phone'], $inYM_phoneList)) {
                $tmpItem = [
                    'name' => $item['name'],
                    'phone' => $item['phone'],
                ];
                array_push($others, $tmpItem);
                array_push($otherPhoneList, $item['phone']);
            }
        }

        //转成字符串：
        $otherPhoneStr = implode(',', $otherPhoneList);

        //获取v_icon医生数据库：
        $in_DoctorVIconDBArr = DoctorVIcon::all()->lists('phone')->toArray();

        //请求240万数据库数据：
        $in_DoctorDBList = GetDoctor::getDoctor($otherPhoneStr, 'phone');
        if ($in_DoctorDBList != false) {
            $otherPart1 = array();
            $otherPart2 = array();
            $otherPart3 = array();
            foreach ($others as $other) {
                if (in_array($other['phone'], $in_DoctorVIconDBArr)) {
                    array_push($otherPart1, $other);
                } elseif (in_array($other['phone'], $in_DoctorDBList)) {
                    array_push($otherPart2, $other);
                } else {
                    array_push($otherPart3, $other);
                }
            }
            $others = array_merge($otherPart1, $otherPart2, $otherPart3);
        }

        $addressBook = DoctorAddressBook::find($userId);
        $addressBook->not_in_ym = $otherPhoneStr;
        $addressBook->save();

        //返回数据:
        $data = [
            'friend_count' => count($inYM),
            'other_count' => count($others),
            'friends' => Transformer::addressBookUsersTransform($inYM),
            'others' => $others
        ];

        return $data;
    }

    /**
     * 给好友发短信邀请加入医脉。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvite(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $phoneList = $request['phone'];
        $phoneArr = explode(',', $phoneList);
        $this->sendSMS($user->name, $phoneArr);

        return response()->json(['success' => ''], 204);
    }

    /**
     * 发送短信给所有未加入医脉的好友。
     *
     * @param $userId
     * @param $userName
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInviteAllFriends($userId, $userName)
    {
        $allFriends = DoctorAddressBook::find($userId);
        $phoneArr = explode(',', $allFriends->not_in_ym);
        $this->sendSMS($userName, $phoneArr);

        return response()->json(['success' => ''], 204);
    }

    /**
     * 发送短信。
     *
     * @param $name
     * @param $phoneArr
     */
    public function sendSMS($name, $phoneArr)
    {
        foreach ($phoneArr as $item) {
            $sms = new Sms();
            $txt = '【医脉】您的好友' . $name . '邀请您加入医脉。URL:'; //文案
            $sms->sendSMS($item, $txt);
        }
    }
}
