<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/9/26
 * Time: 下午6:49
 */

namespace App\Api\Controllers;

use App\Api\Requests\DpCodeRequest;
use App\Api\Requests\SearchDoctorForIdRequest;
use App\Api\Transformers\Transformer;
use App\DoctorContactRecord;
use App\DoctorRelation;
use App\User;

class SearchController extends BaseController
{
    /**
     * Doctors
     *
     * @param SearchDoctorForIdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctors(SearchDoctorForIdRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $idList = explode(',', $request->get('id_list'));
        $newIdList = array();

        /**
         * 自动过滤1-5内置用户：
         */
        foreach ($idList as $item) {
            if ($item > 5) {
                array_push($newIdList, $item);
            }
        }

        $doctors = User::find($newIdList);


        /**
         * 获取辅助数据: 最近通讯记录 / 好友ID列表 / 好友的好友ID列表
         */
        $contactRecords = DoctorContactRecord::where('doctor_id', $user->id)->lists('contacts_id_list');
        $contactRecordsIdList = (count($contactRecords) != 0) ? explode(',', $contactRecords[0]) : $contactRecords;
        $friendsIdList = DoctorRelation::getFriendIdList($user->id);
        $friendsFriendsIdList = DoctorRelation::getFriendsFriendsIdList($friendsIdList, $user->id);

        $data = array();
        foreach ($doctors as $doctor) {
            if (empty($contactRecordsIdList) && in_array($doctor->id, $contactRecordsIdList)) {
                array_push($data, Transformer::searchDoctorTransform_2($doctor, 1));
                continue;
            }

            if (in_array($doctor->id, $friendsIdList)) {
                array_push($data, Transformer::searchDoctorTransform_2($doctor, 1));
                continue;
            }

            if (in_array($doctor->id, $friendsFriendsIdList)) {
                array_push($data, Transformer::searchDoctorTransform_2($doctor, 2));
                continue;
            }

            array_push($data, Transformer::searchDoctorTransform_2($doctor));
        }

        return response()->json(compact('data'));
    }

    /**
     * @param DpCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDoctorInfoForDpCode(DpCodeRequest $request)
    {
        $my = User::getAuthenticatedUser();
        if (!isset($my->id)) {
            return $my;
        }

        $user = User::getDoctorForDpCode($request->get('dp_code'));
        if (isset($user['id']) && $user['id'] != '' && $user['id'] != null) {
            $user['dp_code'] = User::getDpCode($user['id']);
            if((DoctorRelation::getIsFriend($my->id, $user['id'])[0]->count) == 2){
                $user['is_friend'] = true;
                $user['common_friend_list'] = DoctorRelation::getCommonFriendList($my->id, $user['id']);
            }else{
                $user['is_friend'] = false;
            }
            $data = Transformer::searchDoctorTransform_dpCode($user);
        } else {
            $data = '';
        }

        return response()->json(compact('data'));
    }
}
