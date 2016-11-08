<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\TransactionRecordTransformer;
use App\Api\Transformers\WalletTransformer;
use App\DoctorWallet;
use App\Order;
use App\User;
use Illuminate\Http\Request;

class WalletController extends BaseController
{
    /**
     * 钱包基础信息
     *
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function info()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $walletInfo = DoctorWallet::where('doctor_id', $user->id)->first();
        if (!isset($walletInfo->doctor_id)) {
            $walletInfo = DoctorWallet::insert(['doctor_id' => $user->id]);
        }

        $total = Order::totalFeeSum($user->id);
        $billable = Order::billableSum($user->id);
        $pending = Order::pendingSum($user->id);
        $walletInfo->total = ($total[0]->sum_value) / 100;
        $walletInfo->billable = ($billable[0]->sum_value) / 100; //可提现
        $walletInfo->pending = ($pending[0]->sum_value) / 100; //待结算
        //$walletInfo->refunded = 0; //已提现
        $walletInfo->save();

        return $this->response->item($walletInfo, new WalletTransformer());
    }

    /**
     * 收支明细列表 - 带分类
     *
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function record(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        if (isset($request['type'])) {
            $type = $request['type'];
            if ($type == 'billable') { //可提现
                $record = Order::where('doctor_id', $user->id)
                    ->where('settlement_status', '可提现')
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } elseif ($type == 'pending') { //待结算，
                $record = Order::where('doctor_id', $user->id)
                    ->where('settlement_status', '待结算')
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else {
                $record = Order::where('doctor_id', $user->id)
                    ->orderBy('created_at', 'DESC')
                    ->get();
            }
        } else {
            $record = Order::where('doctor_id', $user->id)
                ->orderBy('created_at', 'DESC')
                ->get();
        }

        $data = array();
        foreach ($record as $item) {
            $recordData = TransactionRecordTransformer::transformData($item);
            array_push($data, $recordData);
        }

        return response()->json(compact('data'));
    }

    /**
     * 收支明细列表
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function recordGet()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $record = Order::where('doctor_id', $user->id)->orderBy('created_at', 'DESC')->get();
        $data = array();
        foreach ($record as $item) {
            $recordData = TransactionRecordTransformer::transformData($item);
            array_push($data, $recordData);
        }

        return response()->json(compact('data'));
    }

    /**
     * 收支细节
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function detail($id)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $order = Order::find($id);
        $data = TransactionRecordTransformer::transformData($order);

        return response()->json(compact('data'));
    }
}
