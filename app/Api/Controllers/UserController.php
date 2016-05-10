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
use Intervention\Image\Facades\Image;
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
        if (isset($request['head_img']) && !empty($request['head_img'])) {
            $user->avatar = $this->avatar($user->id, $request->file('head_img'));
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
     * 存储头像文件并压缩成200*200
     * 
     * @param $userId
     * @param $avatarFile
     * @return string
     */
    public function avatar($userId, $avatarFile)
    {
        $destinationPath = 'uploads/avatar/';
        $filename = $userId . '_' . $avatarFile->getClientOriginalName();
        $avatarFile->move($destinationPath, $filename);

        Image::make($destinationPath.$filename)->fit(200)->save();

        return '/' . $destinationPath . $filename;
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
        $sameCityArr = array();
        $b_s_g_threeA = array();
        $otherArr = array();

        foreach ($users as $userItem) {
            $this->groupByCitys($userItem, $citys, $cityIdList);
            $this->groupByProvinces($userItem, $provinces, $provinceIdList);
            $this->groupByHospitals($userItem, $hospitals, $hospitalIdList);
            $this->groupByDepartments($userItem, $departments, $departmentIdList);

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
            'provinces' => $provinces,
            'citys' => $citys,
            'hospitals' => $hospitals,
            'departments' => $departments,
            'count' => count($retData),
            'users' => $retData
        ];
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

            if (isset($hospitals[$userItem->province_id])) {
                if (isset($hospitals[$userItem->province_id][$userItem->city_id])) {
                    array_push(
                        $hospitals[$userItem->province_id][$userItem->city_id],
                        ['id' => $userItem->hospital_id, 'name' => $userItem->hospital]
                    );
                } else {
                    array_push(
                        $hospitals[$userItem->province_id],
                        [$userItem->city_id =>
                            ['id' => $userItem->hospital_id, 'name' => $userItem->hospital]
                        ]
                    );
                }
            } else {
                $hospitals[$userItem->province_id] = [
                    [$userItem->city_id =>
                        ['id' => $userItem->hospital_id, 'name' => $userItem->hospital]
                    ]
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
}
