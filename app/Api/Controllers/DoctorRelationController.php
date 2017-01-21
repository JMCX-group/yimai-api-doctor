<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Helper\MsgAndNotification;
use App\Api\Helper\SmsContent;
use App\Api\Requests\AddressRequest;
use App\Api\Requests\RelationDelRequest;
use App\Api\Requests\RelationIdRequest;
use App\Api\Requests\RemarksRequest;
use App\Api\Transformers\Transformer;
use App\Doctor;
use App\DoctorAddressBook;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\DoctorVIcon;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorRelationController extends BaseController
{
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
            $data['confirm'] = ($friend['verify_switch'] == 0) ? 1 : 0; //是否确认；1：已确认，0：未确认；

            try {
                $relation = DoctorRelation::create($data);
                if ($relation) {
                    /**
                     * 判断被加一方是否无需验证：
                     */
                    if ($friend['verify_switch'] == 0) {
                        $friendData['doctor_id'] = $friend['id'];
                        $friendData['doctor_friend_id'] = $user->id;
                        $friendData['doctor_read'] = 0;
                        $friendData['doctor_friend_read'] = 1;
                        $friendData['confirm'] = 1;
                        DoctorRelation::create($friendData);
                    }

                    /**
                     * 推送相关信息：
                     */
                    $doctor = Doctor::find($request['id']);
                    if (isset($doctor->id) && ($doctor->device_token != '' && $doctor->device_token != null)) {
                        MsgAndNotification::pushAddFriendMsg($doctor->device_token, $request['id']); //向相关医生推送消息
                    }

                    return response()->json(['success' => ''], 204);
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
            $data['confirm'] = 0; //是否确认；1：已确认，0：未确认；

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

        /**
         * 查询是否有这条数据以及更新已读情况：
         */
        $relation = DoctorRelation::where('doctor_id', $request['id'])
            ->where('doctor_friend_id', $user->id)
            ->first();

        if ($relation) {
            $data = [
                'doctor_id' => $user->id,
                'doctor_friend_id' => $request['id'],
                'doctor_read' => 1,
                'doctor_friend_read' => 0,
                'confirm' => 1 //是否确认；1：已确认，0：未确认；
            ];

            try {
                if (DoctorRelation::create($data)) {
                    /**
                     * 更新已读和关系确认情况：
                     */
                    $relation->doctor_friend_read = 1;
                    $relation->confirm = 1;
                    $relation->save();

                    /**
                     * 推送相关信息：
                     */
                    $doctor = Doctor::find($request['id']);
                    if (isset($doctor->id) && ($doctor->device_token != '' && $doctor->device_token != null)) {
                        MsgAndNotification::pushAddFriendMsg($doctor->device_token, $request['id']); //向相关医生推送消息
                    }

                    return response()->json(['success' => ''], 204);
                } else {
                    return response()->json(['message' => '添加失败'], 500);
                }
            } catch (\Exception $e) {
                Log::info('add friend', ['context' => $e->getMessage()]);
                return response()->json(['message' => '添加失败'], 400);
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
     * @param RelationDelRequest $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function destroy(RelationDelRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        try {
            DoctorRelation::where('doctor_id', $user->id)->where('doctor_friend_id', $request['friend_id'])->delete();
            DoctorRelation::where('doctor_friend_id', $user->id)->where('doctor_id', $request['friend_id'])->delete();

            return response()->json(['success' => ''], 204);
        } catch (\Exception $e) {
            Log::info('del friend', ['context' => $e->getMessage()]);
            return response()->json(['message' => '删除失败'], 500);
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
        //获取好友（包含对方未确认的）列表:
        $friendsIdList = DoctorRelation::getAllFriendsIdList($userId);
        $friends = User::whereIn('id', $friendsIdList)->get();

        //获取电话列表:
        $phoneArr = array();
        foreach ($content as $item) {
            array_push($phoneArr, $item['phone']);
        }
        $allFriends = User::whereIn('phone', $phoneArr)->get();

        //找到通讯录中已在医脉加过的（包含对方未确认的）好友:
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
//        $in_DoctorDBList = GetDoctor::getDoctor($otherPhoneStr, 'phone');
//        if ($in_DoctorDBList != false) {
        $otherPart1 = array();
        $otherPart2 = array();
        $otherPart3 = array();
        foreach ($others as $other) {
            if (in_array($other['phone'], $in_DoctorVIconDBArr)) {
                array_push($otherPart1, $other);
//                } elseif (in_array($other['phone'], $in_DoctorDBList)) {
//                    array_push($otherPart2, $other);
            } else {
                array_push($otherPart3, $other);
            }
        }
        $others = array_merge($otherPart1, $otherPart2, $otherPart3);
//        }

        $addressBook = DoctorAddressBook::find($userId);
        $addressBook->not_in_ym = $otherPhoneStr;
        $addressBook->save();

        //增加已发送状态：
        $tmpSmsSentArr = explode(',', $addressBook->sms_sent);
        $othersPartYes = array();
        $othersPartNo = array();
        foreach ($others as &$other) {
            $tmpOtherItem = [
                'name' => $other['name'],
                'phone' => $other['phone'],
                'sms_status' => 'false'
            ];
            if (in_array($other['phone'], $tmpSmsSentArr)) {
                $tmpOtherItem['sms_status'] = 'true';
                array_push($othersPartYes, $tmpOtherItem);
            } else {
                array_push($othersPartNo, $tmpOtherItem);
            }
        }
        $others = array_merge($othersPartNo, $othersPartYes);

        //返回数据:
        $data = [
            'friend_count' => count($inYM),
            'other_count' => count($others),
            'friends' => Transformer::addressBookUsersTransform($inYM),
            'others' => $others,
//            'error' => ($in_DoctorDBList != false) ? '' : $in_DoctorDBList
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
        SmsContent::sendSms_invite(User::getDpCode($user->id), $user->name, $phoneArr);

        /**
         * 记录是否发过短信：
         */
        $doctorAddressBook = DoctorAddressBook::find($user->id);
        $tmpSmsSentList = $doctorAddressBook->sms_sent;
        $tmpSmsSentArr = explode(',', $tmpSmsSentList);
        if ($tmpSmsSentList == null || $tmpSmsSentList == '') {
            $tmpSmsSentList = $phoneList;
        } else {
            foreach ($phoneArr as $item) {
                if (!in_array($item, $tmpSmsSentArr)) {
                    array_push($tmpSmsSentArr, $item);
                }
            }
            $tmpSmsSentList = implode(',', $tmpSmsSentArr);
        }
        $doctorAddressBook->sms_sent = $tmpSmsSentList;
        $doctorAddressBook->sms_sent_time = date('Y-m-d H:i:s', time());
        $doctorAddressBook->save();

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
        SmsContent::sendSms_invite(User::getDpCode($userId), $userName, $phoneArr);

        return response()->json(['success' => ''], 204);
    }
}
