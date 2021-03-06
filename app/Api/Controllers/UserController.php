<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:39
 */

namespace App\Api\Controllers;

use App\Api\Helper\GetDoctor;
use App\Api\Helper\MsgAndNotification;
use App\Api\Helper\RongCloudServerAPI;
use App\Api\Helper\SaveImage;
use App\Api\Requests\SearchUserRequest;
use App\Api\Requests\UserRequest;
use App\Api\Transformers\Transformer;
use App\Api\Transformers\UserTransformer;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\DoctorVIcon;
use App\Hospital;
use App\InvitedDoctor;
use App\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    private $rongYunSer;

    public function __construct()
    {
        $this->rongYunSer = new RongCloudServerAPI();
    }

    /**
     * 上传认证需要的图片。
     *
     * @param Request $request
     * @return array
     */
    public function uploadAuthPhotos(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $imgUrl_1 = isset($request['img-1']) ? SaveImage::auth($user->id, $request->file('img-1')) : '';
        $imgUrl_2 = isset($request['img-2']) ? SaveImage::auth($user->id, $request->file('img-2'), 2) : '';
        $imgUrl_3 = isset($request['img-3']) ? SaveImage::auth($user->id, $request->file('img-3'), 3) : '';
        $imgUrl_4 = isset($request['img-4']) ? SaveImage::auth($user->id, $request->file('img-4'), 4) : '';
        $imgUrl_5 = isset($request['img-5']) ? SaveImage::auth($user->id, $request->file('img-5'), 5) : '';

        $user->auth_img = ($imgUrl_1 != '') ? $imgUrl_1 . ',' : '';
        $user->auth_img .= ($imgUrl_2 != '') ? $imgUrl_2 . ',' : '';
        $user->auth_img .= ($imgUrl_3 != '') ? $imgUrl_3 . ',' : '';
        $user->auth_img .= ($imgUrl_4 != '') ? $imgUrl_4 . ',' : '';
        $user->auth_img .= ($imgUrl_5 != '') ? $imgUrl_5 . ',' : '';
        $user->auth_img = substr($user->auth_img, 0, strlen($user->auth_img) - 1);

        $user->auth = 'processing'; //未认证： ；认证成功：completed；认证中：processing；认证失败：fail；
        $user->save();

        /**
         * 如果是被邀请的，则更新状态：
         */
        $invitedDoctor = InvitedDoctor::where('doctor_id', $user->id)->first();
        if (!empty($invitedDoctor)) {
            $invitedDoctor->status = 'processing'; //wait：等待邀请；invited：已邀请/未加入；re-invite：可以重新邀请了；join：已加入；processing：认证中；completed：完成认证
            $invitedDoctor->save();
        }

        return ['url' => $user->auth_img];
    }

    /**
     * Update user info.
     *
     * @param UserRequest $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|void
     */
    public function update(UserRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        if (isset($request['email']) && !empty($request['email'])) {
            $user->email = $request['email'];
        }
        if (isset($request['password']) && !empty($request['password'])) {
            $user->password = bcrypt($request->get('password'));
        }

        // device_token : 友盟消息推送服务对设备的唯一标识。Android的device_token是44位字符串, iOS的device-token是64位
        if (isset($request['device_token']) && !empty($request['device_token'])) {
            $user->device_token = $request['device_token'];
        }

        if (isset($request['name']) && !empty($request['name']) && $user->auth != 'completed' && $user->auth != 'processing') {
            $user->name = $request['name'];
            $this->rongYunSer->userRefresh($user->id, $user->name, $user->avatar); //更新融云用户信息

            /**
             * 匹配医生数据库的直接给予认证标志：
             */
            $doctorVIcon = DoctorVIcon::where('phone', $user->phone)->where('name', $user->name)->first();
            if ($doctorVIcon) {
                $user->auth = 'completed';
            }
        }
        if (isset($request['head_img']) && !empty($request['head_img'])) {
            $user->avatar = SaveImage::avatar($user->id, $request->file('head_img'));
            $this->rongYunSer->userRefresh($user->id, $user->name, $user->avatar); //更新融云用户信息
        }
        // 传0会判断成false,需要判断:
        if (isset($request['sex']) && (!empty($request['sex']) || $request['sex'] == 0)) {
            // 1:男; 0:女
            $user->gender = $request['sex'];
        }
        if (isset($request['province']) && !empty($request['province'])) {
            $user->province_id = $request['province'];
        }
        if (isset($request['city']) && !empty($request['city'])) {
            $user->city_id = $request['city'];
        }
        if (isset($request['hospital']) && !empty($request['hospital'])) {
            $hospitalId = $request['hospital'];
            if (!is_numeric($request['hospital'])) {
                $hospitalId = $this->createNewHospital($request);
            }
            $user->hospital_id = $hospitalId;
            $hospitals = Hospital::find($hospitalId);
            $user->province_id = $hospitals->province_id;
            $user->city_id = $hospitals->city_id;
        }
        if (isset($request['department']) && !empty($request['department'])) {
            $user->dept_id = $request['department'];
        }
        if (isset($request['job_title']) && !empty($request['job_title'])) {
            $user->title = $request['job_title'];
        }
        if (isset($request['college']) && !empty($request['college'])) {
            $user->college_id = $request['college'];
        }
        if (isset($request['ID_number']) && !empty($request['ID_number'])) {
            $user->id_num = $request['ID_number'];
        }
        if (isset($request['tags']) && !empty($request['tags'])) {
            $user->tag_list = $request['tags'];
        }
        if (isset($request['personal_introduction'])) {
            $user->profile = $request['personal_introduction'];
        }

        /**
         * 接诊收费设置。
         */
        if (isset($request['fee_switch']) && (!empty($request['fee_switch']) || $request['fee_switch'] == 0)) {
            $user->fee_switch = $request['fee_switch'];
        }
        if (isset($request['fee']) && !empty($request['fee'])) {
            $user->fee = $request['fee'];
        }
        if (isset($request['fee_face_to_face']) && !empty($request['fee_face_to_face'])) {
            $user->fee_face_to_face = $request['fee_face_to_face'];
        }
        if (isset($request['admission_set_fixed']) && !empty($request['admission_set_fixed'])) {
            $user->admission_set_fixed = $request['admission_set_fixed'];
        }
        if (isset($request['admission_set_flexible']) && !empty($request['admission_set_flexible'])) {
            $user->admission_set_flexible = $this->delOutdated(json_decode($request['admission_set_flexible'], true));
        }

        /**
         * 隐私设置: 加好友验证开关 | 好友的好友发起约诊开关。
         */
        if (isset($request['verify_switch']) && (!empty($request['verify_switch']) || $request['verify_switch'] == 0)) {
            $user->verify_switch = $request['verify_switch'];
        }
        if (isset($request['friends_friends_appointment_switch']) && (!empty($request['friends_friends_appointment_switch']) || $request['friends_friends_appointment_switch'] == 0)) {
            $user->friends_friends_appointment_switch = $request['friends_friends_appointment_switch'];
        }

        /**
         * Generate dp code.
         */
        if (empty($user->dp_code) && !empty($user->dept_id)) {
            $user->dp_code = User::generateDpCode($user->dept_id);
        }

        /**
         * Generate qr code.
         */
        if (empty($user->qr_code_url) || $user->qr_code_url == '' || $user->qr_code_url == null) {
            $qrData = [
                'data' => $user->id,
                'operation' => 'add_friend'
            ];
            QrCode::format('png')->size(200)->generate(json_encode($qrData), 'qrcode/' . $user->id . '.png');
            $user->qr_code_url = '/qrcode/' . $user->id . '.png';
        }

        /**
         * Address.
         */
        if (isset($request['address']) && !empty($request['address'])) {
            $user->address = $request['address'];
        }
        if (isset($request['addressee']) && !empty($request['addressee'])) {
            $user->addressee = $request['addressee'];
        }
        if (isset($request['receive_phone']) && !empty($request['receive_phone'])) {
            $user->receive_phone = $request['receive_phone'];
        }

        /**
         * Blacklist
         */
        if (isset($request['blacklist'])) {
            $user->blacklist = $request['blacklist'];
        }

        /**
         * Common text
         */
        if (isset($request['common_text'])) {
            $user->common_text = $request['common_text'];
        }

        /**
         * Get rong yun token.
         */
        if (($user->rong_yun_token == '' || $user->rong_yun_token == null) && ($user->name != '' && $user->name != null)) {
            //获取云信token
            $rongCloudRet = $this->rongYunSer->getToken($user->id, $user->name, $user->avatar);
            $rongCloudRet = json_decode($rongCloudRet, true);
            if ($rongCloudRet['code'] == 200 && $rongCloudRet['userId'] == $user->id) {
                $user->rong_yun_token = $rongCloudRet['token'];
            }

            $this->rongYunSer->userRefresh($user->id, $user->name, $user->avatar); //更新融云用户信息

            /**
             * 有了融云信息，则表示已经更新过一次信息，则进行自动添加邀请人：
             */
            if ($user->inviter_dp_code != '' && $user->inviter_dp_code != null) {
                $inviter = User::getInviter($user->inviter_dp_code);
                if (!$inviter) { //有可能是合作专区健康顾问码
                    $this->autoAddFriend($user->id, $inviter);
                }
            }
        }

        try {
            if ($user->save()) {
                return $this->response->item($user, new UserTransformer());
            } else {
                return $this->response->error('unknown error', 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    /**
     * 自动和邀请者添加好友
     *
     * @param $myId
     * @param $friendInfo
     */
    public function autoAddFriend($myId, $friendInfo)
    {
        try {
            /**
             * 增加自己和好友的关系
             */
            $dr = DoctorRelation::where('doctor_id', $myId)->where('doctor_friend_id', $friendInfo->id)->first();
            if (!$dr) {
                $data['doctor_id'] = $myId;
                $data['doctor_friend_id'] = $friendInfo->id;
                $data['doctor_read'] = 1;
                $data['doctor_friend_read'] = 0;
                $data['confirm'] = 1;
                DoctorRelation::create($data);
            }

            /**
             * 增加好友和自己的关系
             */
            $dr2 = DoctorRelation::where('doctor_id', $friendInfo->id)->where('doctor_friend_id', $myId)->first();
            if (!$dr2) {
                $friendData['doctor_id'] = $friendInfo->id;
                $friendData['doctor_friend_id'] = $myId;
                $friendData['doctor_read'] = 0;
                $friendData['doctor_friend_read'] = 0;
                $friendData['confirm'] = 1;
                DoctorRelation::create($friendData);
            }

            /**
             * 推送相关信息：
             */
            if (isset($friendInfo->id) && ($friendInfo->device_token != '' && $friendInfo->device_token != null)) {
                MsgAndNotification::pushAddFriendMsg($friendInfo->device_token, $friendInfo->id); //向相关医生推送消息
            }
        } catch (\Exception $e) {
            Log::info('auto-add-friend', ['context' => $e->getMessage()]);
        }
    }

    /**
     * 删除过期时间
     *
     * @param $data
     * @return string
     */
    public function delOutdated($data)
    {
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
     * Create new hospital.
     *
     * @param $request
     * @return string|static
     */
    public function createNewHospital($request)
    {
        $data = [
            'province' => $request['province'],
            'city' => $request['city'],
            'name' => $request['hospital']
        ];

        return Hospital::firstOrCreate($data)->id;
    }

    /**
     * @param SearchUserRequest $request
     * @return mixed
     */
    public function searchUser_admissions(SearchUserRequest $request)
    {
        return $this->searchUser($request, 'admissions');
    }

    /**
     * @param SearchUserRequest $request
     * @return mixed
     */
    public function searchUser_sameHospital(SearchUserRequest $request)
    {
        return $this->searchUser($request, 'same_hospital');
    }

    /**
     * @param SearchUserRequest $request
     * @return mixed
     */
    public function searchUser_sameDept(SearchUserRequest $request)
    {
        return $this->searchUser($request, 'same_department');
    }

    /**
     * @param SearchUserRequest $request
     * @return mixed
     */
    public function searchUser_sameCollege(SearchUserRequest $request)
    {
        return $this->searchUser($request, 'same_college');
    }

    /**
     * Search for doctors.
     * Order by.
     *
     * @param SearchUserRequest $request
     * @param null $type
     * @return array
     */
    public function searchUser(SearchUserRequest $request, $type = null)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        if (isset($request['page']) && !empty($request['page'])) {
            $page = $request['page'];
        } else {
            $page = 1;
        }

        /**
         * 获取前台传参:
         * 兼容不同形式的……蛋疼:
         */
        if (isset($request['city']) && !empty($request['city'])) {
            $cityID = $request['city'];
        } elseif (isset($request['city_id']) && !empty($request['city_id'])) {
            $cityID = $request['city_id'];
        } else {
            $cityID = false;
        }
        if (isset($request['hospital']) && !empty($request['hospital'])) {
            $hospitalID = $request['hospital'];
        } elseif (isset($request['hospital_id']) && !empty($request['hospital_id'])) {
            $hospitalID = $request['hospital_id'];
        } else {
            $hospitalID = false;
        }
        if (isset($request['department']) && !empty($request['department'])) {
            $deptID = $request['department'];
        } elseif (isset($request['dept_id']) && !empty($request['dept_id'])) {
            $deptID = $request['dept_id'];
        } else {
            $deptID = false;
        }
        $data = [
            'field' => isset($request['field']) && !empty($request['field']) ? $request['field'] : false,
            'city_id' => $cityID,
            'hospital_id' => $hospitalID,
            'dept_id' => $deptID
        ];

        /**
         * 获取基础数据: 符合条件的所有医生数据.
         *
         * type参数 ( false: 不传或传空的默认值) :
         * same_hospital: 医脉处进行同医院搜索;
         *
         */
        $searchType = (($type == null || $type == '') && isset($request['type']) && !empty($request['type'])) ? $request['type'] : $type;

        switch ($searchType) {
            case 'admissions':
                $users = User::searchDoctor_admissions($user->id, $data['field'], $user->city_id);
                break;
            case 'same_hospital':
                $users = User::searchDoctor_sameHospital($user->id, $data['field'], $user->hospital_id, $data['city_id'], $data['dept_id']);
                break;
            case 'same_department':
//                $deptIdList = DeptStandard::getSameFirstLevelDeptIdList($user->dept_id);
                $users = User::searchDoctor_sameDept($user->id, $data['field'], $user->dept_id, $data['city_id'], $data['hospital_id']);
                break;
            case 'same_college':
                $users = User::searchDoctor_sameCollege($user->id, $data['field'], $user->college_id, $data['city_id'], $data['hospital_id'], $data['dept_id']);
                break;
            default:
                $users = User::searchDoctor($user->id, $data['field'], $data['city_id'], $data['hospital_id'], $data['dept_id']);
                break;
        }

        /**
         * 获取辅助数据: 最近通讯记录 / 好友ID列表 / 好友的好友ID列表
         */
        $contactRecords = DoctorContactRecord::where('doctor_id', $user->id)->lists('contacts_id_list');
        $contactRecordsIdList = (count($contactRecords) != 0) ? explode(',', $contactRecords[0]) : $contactRecords;
        $friendsIdList = DoctorRelation::getFriendIdList($user->id);
        $friendsFriendsIdList = DoctorRelation::getFriendsFriendsIdList($friendsIdList, $user->id);

        /**
         * 排序/分组
         * 规则: 最近互动 + 共同好友 + 同城 + 北上广三甲 + 180天内累计约诊次数
         */
        $provinces = array();
        $citys = array();
        $hospitals = array();
        $departments = array();
        $cityIdList = array();
        $provinceIdList = array();
        $hospitalIdList = array();
        $departmentIdList = array();

        $recentContactsArr = array();
        $friendArr = array();
        $friendsFriendsArr = array();
        $sameCityArr = array();
        $b_s_g_threeA = array();
        $otherArr = array();

        foreach ($users as $userItem) {
            $this->groupByCitys($userItem, $citys, $cityIdList);
            $this->groupByProvinces($userItem, $provinces, $provinceIdList);
            $this->groupByHospitals($userItem, $hospitals, $hospitalIdList);
            $this->groupByDepartments($userItem, $departments, $departmentIdList);

            if (empty($contactRecordsIdList) && in_array($userItem->id, $contactRecordsIdList)) {
                array_push($recentContactsArr, Transformer::searchDoctorTransform($userItem, 1));
                continue;
            }

            if (in_array($userItem->id, $friendsIdList)) {
                array_push($friendArr, Transformer::searchDoctorTransform($userItem, 1));
                continue;
            }

            if (in_array($userItem->id, $friendsFriendsIdList)) {
                array_push($friendsFriendsArr, Transformer::searchDoctorTransform($userItem, 2));
                continue;
            }

            if ($user->city_id == $userItem->city_id) {
                array_push($sameCityArr, Transformer::searchDoctorTransform($userItem));
                continue;
            }

            if (in_array($userItem->city, array('北京', '上海', '广州'))) {
                array_push($b_s_g_threeA, Transformer::searchDoctorTransform($userItem));
                continue;
            }

            array_push($otherArr, Transformer::searchDoctorTransform($userItem));
        }

        /**
         * 把医院数据格式特殊处理:
         */
        if (isset($request['format']) && $request['format'] == 'android') {
            $newHospital = array();
            foreach ($hospitals as $key => $val) {
                $newCityList = [
                    'province_id' => $key,
                    'data' => []
                ];
                foreach ($val as $keyItem => $valItem) {
                    $newHospitalList = [
                        'city_id' => $keyItem,
                        'data' => $valItem
                    ];
                    array_push($newCityList['data'], $newHospitalList);
                }
                array_push($newHospital, $newCityList);
            }
        }

        /**
         * 只有普通搜索有分组:
         */
        if ($request['type'] == 'same_hospital' || $request['type'] == 'same_department' || $request['type'] == 'same_college' || ($type != null && $type != 'admissions')) {
            $retData = array_merge($recentContactsArr, $friendArr, $friendsFriendsArr, $sameCityArr, $b_s_g_threeA, $otherArr);

            return [
                'provinces' => $provinces,
                'citys' => $citys,
                'hospitals' => isset($newHospital) ? $newHospital : $hospitals,
                'departments' => $departments,
                'count' => count($retData),
                'users' => $retData
            ];
        } else {
            if ($type == 'admissions') {
                $retData_1 = array_merge($recentContactsArr, $friendArr);
                $retData_2 = $friendsFriendsArr;
                $retData_other = array();
            } else {
                $retData_1 = array_merge($recentContactsArr, $friendArr);
                $retData_2 = $friendsFriendsArr;
                $retData_other = array_merge($sameCityArr, $b_s_g_threeA, $otherArr);
            }

            return [
                'provinces' => $provinces,
                'citys' => $citys,
                'hospitals' => isset($newHospital) ? $newHospital : $hospitals,
                'departments' => $departments,
                'count' => (count($retData_1) + count($retData_2) + count($retData_other)),
                'users' => [
                    'friends' => array_slice($retData_1, ($page - 1) * 100, 100),
                    'friends-friends' => array_slice($retData_2, ($page - 1) * 100, 100),
                    'other' => array_slice($retData_other, ($page - 1) * 100, 100)
                ]
            ];
        }
    }

    /**
     * 将城市按省分组
     *
     * @param $userItem
     * @param $citys
     * @param $cityIdList
     */
    public function groupByCitys($userItem, &$citys, &$cityIdList)
    {
        if (!in_array($userItem->city_id, $cityIdList)) {
            array_push($cityIdList, $userItem->city_id);
            if (isset($citys[$userItem->province_id])) {
                array_push(
                    $citys[$userItem->province_id],
                    ['id' => $userItem->city_id, 'name' => $userItem->city]
                );
            } else {
                $citys[$userItem->province_id] = [
                    ['id' => $userItem->city_id, 'name' => $userItem->city]
                ];
            }
        }
    }

    /**
     * @param $userItem
     * @param $provinces
     * @param $provinceIdList
     */
    public function groupByProvinces($userItem, &$provinces, &$provinceIdList)
    {
        if (!in_array($userItem->province_id, $provinceIdList)) {
            array_push($provinceIdList, $userItem->province_id);
            array_push(
                $provinces,
                ['id' => $userItem->province_id, 'name' => $userItem->province]
            );
        }
    }

    /**
     * 将医院按省和市层级分组
     *
     * @param $userItem
     * @param $hospitals
     * @param $hospitalIdList
     */
    public function groupByHospitals($userItem, &$hospitals, &$hospitalIdList)
    {
        if (!in_array($userItem->hospital_id, $hospitalIdList)) {
            array_push($hospitalIdList, $userItem->hospital_id);

            if (isset($hospitals[$userItem->province_id]) && isset($hospitals[$userItem->province_id][$userItem->city_id])) {
                array_push(
                    $hospitals[$userItem->province_id][$userItem->city_id],
                    ['id' => $userItem->hospital_id, 'name' => $userItem->hospital,
                        'province_id' => $userItem->province_id, 'city_id' => $userItem->city_id]
                );
            } else {
                $hospitals[$userItem->province_id][$userItem->city_id] = [
                    ['id' => $userItem->hospital_id, 'name' => $userItem->hospital,
                        'province_id' => $userItem->province_id, 'city_id' => $userItem->city_id]
                ];
            }
        }
    }

    /**
     * @param $userItem
     * @param $departments
     * @param $departmentIdList
     */
    public function groupByDepartments($userItem, &$departments, &$departmentIdList)
    {
        if (!in_array($userItem->dept_id, $departmentIdList)) {
            array_push($departmentIdList, $userItem->dept_id);
            array_push(
                $departments,
                ['id' => $userItem->dept_id, 'name' => $userItem->dept]
            );
        }
    }

    /**
     * 查看其他医生的主页所需的信息
     *
     * @param $id
     * @return array|mixed
     */
    public function findDoctor($id)
    {
        $my = User::getAuthenticatedUser();
        if (!isset($my->id)) {
            return $my;
        }

        $user = User::findDoctor($id);
        $user['dp_code'] = User::getDpCode($id);
        $user['is_friend'] = (DoctorRelation::getIsFriend($my->id, $id)[0]->count) == 2 ? true : false;
        $user['common_friend_list'] = DoctorRelation::getCommonFriendList($my->id, $id);

        return Transformer::findDoctorTransform($user);
    }

    /**
     * 通过手机号查看其他医生的信息
     *
     * @param $phone
     * @return array|mixed
     */
    public function findDoctor_byPhone($phone)
    {
        $my = User::getAuthenticatedUser();
        if (!isset($my->id)) {
            return $my;
        }

        $user = User::findDoctor_byPhone($phone);
        if (isset($user['id']) && $user['id'] != '' && $user['id'] != null) {
            $user['dp_code'] = User::getDpCode($user['id']);
            $user['is_friend'] = (DoctorRelation::getIsFriend($my->id, $user['id'])[0]->count) == 2 ? true : false;
            $user['common_friend_list'] = DoctorRelation::getCommonFriendList($my->id, $user['id']);

            return Transformer::findDoctorTransform($user);
        } else {
            return response()->json(['success' => ''], 204); //给肠媳适配。。
        }
    }

    /**
     * 验证是否有在库里的医生。
     *
     * @param Request $request
     * @return string
     */
    public function verifyDoctor(Request $request)
    {
        $retData = GetDoctor::getDoctor($request['phone']);
        $doctors = $retData['data'];
        $data = [
            'in_count' => count(explode(',', $request['phone'])),
            'out_count' => count($doctors),
            'doctor' => $doctors,
            'debug' => $retData['debug']
        ];

//        DoctorDb::insert($doctors);

        //TODO 还未完成
        return response()->json(compact('data'));
    }
}
