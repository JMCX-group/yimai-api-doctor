<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:39
 */

namespace App\Api\Controllers;

use App\Api\Requests\UserRequest;
use App\Api\Transformers\Transformer;
use App\Api\Transformers\UserTransformer;
use App\DeptStandard;
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
        if (empty($user->dp_code) && !empty($user->city_id) && !empty($user->dept_id)) {
            $user->dp_code = User::generateDpCode($user->city_id, $user->dept_id);
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
     * Search for doctors and grouping.
     *
     * @param $data
     * @return array|mixed
     */
    public function searchUser($data)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        /*
         * 获取姓名/医院/科室/特长标签中的有各个数据list;
         * 合并4个list.
         */
        $dataList1 = User::where('name', 'like', '%' . $data . '%')->get();

        $hospitalIdList = Hospital::where('name', 'like', '%' . $data . '%')->lists('id')->toArray();
        $dataList2 = User::whereIn('hospital_id', $hospitalIdList)->get();

        $deptIdList = DeptStandard::where('name', 'like', '%' . $data . '%')->lists('id')->toArray();
        $dataList3 = User::whereIn('dept_id', $deptIdList)->get();

        $dataList4 = User::where('tag_list', 'like', '%' . $data . '%')->get();

        $dataList = $dataList1->merge($dataList2)->merge($dataList3)->merge($dataList4);

        $friends = DoctorRelation::getFriends($user->id);
        $friendsFriends = DoctorRelation::getFriendsFriends($user->id)['user'];

        /*
         * 分组成好友/好友的好友/其他:
         */
        $friendArr = array();
        $friendsFriendArr = array();
        $OtherArr = array();
        foreach ($dataList as $item) {
            foreach ($friends as $friend) {
                if ($item->id == $friend->id) {
                    array_push($friendArr, $item);
                    continue 2;
                    break;
                }
            }

            foreach ($friendsFriends as $friendsFriend) {
                if ($item->id == $friendsFriend->id) {
                    array_push($friendsFriendArr, $item);
                    continue 2;
                    break;
                }
            }

            array_push($OtherArr, $item);
        }

        return [
            'friends' => Transformer::userListTransform($friendArr)['friends'],
            'friends-friends' => Transformer::userListTransform($friendsFriendArr)['friends'],
            'others' => Transformer::userListTransform($OtherArr)['friends']
        ];
    }
}
