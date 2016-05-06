<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:39
 */

namespace App\Api\Controllers;

use App\Api\Requests\SearchUserRequest;
use App\Api\Requests\UserRequest;
use App\Api\Transformers\Transformer;
use App\Api\Transformers\UserTransformer;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\Hospital;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Validator;
use JWTAuth;

class UserController extends BaseController
{
    /**
     * Update user info.
     *
     * @param UserRequest $request
     * @return \Dingo\Api\Http\Response|void
     */
    public function update(UserRequest $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent'], $e->getStatusCode());
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $user->name = $request['name'];
        }
        if (isset($request['sex']) && !empty($request['sex'])) {
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
        if (isset($request['personal_introduction']) && !empty($request['personal_introduction'])) {
            $user->profile = $request['personal_introduction'];
        }

        // Generate dp code.
        if (empty($user->dp_code) && !empty($user->dept_id)) {
            $user->dp_code = User::generateDpCode($user->dept_id);
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
     * Search for doctors.
     * Order by.
     *
     * @param SearchUserRequest $request
     * @return mixed
     */
    public function searchUser(SearchUserRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = [
            'field' => $request['field'],
            'city_id' => isset($request['city']) ? $request['city'] : false,
            'hospital_id' => isset($request['hospital']) ? $request['hospital'] : false,
            'dept_id' => isset($request['department']) ? $request['department'] : false
        ];

        /**
         * 获取基础数据:
         */
        $users = User::searchDoctor($data['field'], $data['city_id'], $data['hospital_id'], $data['dept_id']);

        /**
         * 获取辅助数据:
         */
        $contactRecords = DoctorContactRecord::where('doctor_id', $user->id)->lists('contacts_id_list');
        $contactRecordsIdList = (count($contactRecords) != 0) ? explode(',', $contactRecords[0]) : $contactRecords;

        $friendsIdList = DoctorRelation::getFriendIdList($user->id);

        /**
         * 排序
         * 规则: 最近互动 + 共同好友 + 同城 + 北上广三甲 + 180天内累计约诊次数
         */
        $recentContactsArr = array();
        $friendArr = array();
        $sameCityArr = array();
        $b_s_g_threeA = array();
        $otherArr = array();
        foreach ($users as $userItem) {
            if (in_array($userItem->id, $contactRecordsIdList)) {
                array_push($recentContactsArr, Transformer::searchDoctorTransform($userItem));
                continue;
            }

            if (in_array($userItem->id, $friendsIdList)) {
                array_push($friendArr, Transformer::searchDoctorTransform($userItem));
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

        $retData = array_merge($recentContactsArr, $friendArr, $sameCityArr, $b_s_g_threeA, $otherArr);

        return [
            'count' => count($retData),
            'users' => $retData
        ];
    }
}
