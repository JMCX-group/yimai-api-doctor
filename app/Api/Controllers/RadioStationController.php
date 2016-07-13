<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Transformers\RadioStationTransformer;
use App\RadioRead;
use App\RadioStation;
use App\User;
use Illuminate\Http\Request;

class RadioStationController extends BaseController
{
    /**
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function index()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        // 分页获取广播列表,并左连接获取广播已读状态表信息
        $radioStations = RadioStation::leftJoin('radio_read', function ($join) use ($user) {
            $join->on('radio_stations.id', '=', 'radio_read.radio_station_id')
                ->where('radio_read.user_id', '=', $user->id);
        })
            ->where('status', 0)
//            ->where('valid', '>', date('Y-m-d H:i:s'))
            ->paginate(4);

        return $this->response->paginator($radioStations, new RadioStationTransformer());
    }

    /**
     * 已读后删除对应消息
     * 
     * @param Request $request
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function readStatus(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        RadioRead::where('user_id', $user->id)
            ->where('radio_station_id', $request->id)
            ->delete();

//        return $this->response->noContent();
        return response()->json(['success' => ''], 204); //给肠媳适配。。
    }
}
