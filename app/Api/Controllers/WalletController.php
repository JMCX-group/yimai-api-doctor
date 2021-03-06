<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\IdRequest;
use App\Api\Transformers\TransactionRecordTransformer;
use App\Api\Transformers\WalletTransformer;
use App\AppointmentFee;
use App\DoctorWallet;
use App\SettlementRecord;
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

        /**
         * 激活计算上个月结余：
         */
        $this->checkout($user->id);

        /**
         * 钱包信息生成：
         */
        $walletInfo = DoctorWallet::where('doctor_id', $user->id)->first();
        if (!isset($walletInfo->doctor_id)) {
            $walletInfo = new DoctorWallet();
            $walletInfo->doctor_id = $user->id;
            $walletInfo->save();
        }

        /**
         * 新的通过钱包余额支付的：
         */
        $totalFee = AppointmentFee::totalFeeSum($user->id);
        $billableFee = AppointmentFee::selectSum($user->id, '可提现');
        $pendingFee = AppointmentFee::selectSum($user->id, '待结算');
        $refundedFee = AppointmentFee::selectSum($user->id, '已提现');

        $walletInfo->total = $totalFee[0]->sum_value / 100;
        $walletInfo->billable = $billableFee[0]->sum_value / 100; //可提现
        $walletInfo->pending = $pendingFee[0]->sum_value / 100; //待结算
        $walletInfo->refunded = $refundedFee[0]->sum_value / 100; //已提现
        $walletInfo->save();

        return $this->response->item($walletInfo, new WalletTransformer());
    }

    /**
     * 收支明细列表 - 带分类
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function record(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        if (isset($request['type']) && $request['type'] == 'billable') {
            $record = AppointmentFee::where('doctor_id', $user->id)
                ->where('status', '!=', 'paid')
                ->where('settlement_status', '可提现')
                ->orderBy('created_at', 'DESC')
                ->get();
        } elseif (isset($request['type']) && $request['type'] == 'pending') {
            $record = AppointmentFee::where('doctor_id', $user->id)
                ->where('status', '!=', 'paid')
                ->where('settlement_status', '待结算')
                ->orderBy('created_at', 'DESC')
                ->get();
        } else {
            $record = AppointmentFee::where('doctor_id', $user->id)
                ->where('status', '!=', 'paid')
                ->orderBy('created_at', 'DESC')
                ->get();
        }

        $data = array();
        foreach ($record as $item) {
            $recordData = TransactionRecordTransformer::transformData_fee($item);
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

        $record = AppointmentFee::where('doctor_id', $user->id)
            ->where('status', '!=', 'paid')
            ->orderBy('created_at', 'DESC')
            ->get();
        $data = array();
        foreach ($record as $item) {
            $recordData = TransactionRecordTransformer::transformData_fee($item);
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

        $order = AppointmentFee::find($id);
        $data = TransactionRecordTransformer::transformData_fee($order);

        return response()->json(compact('data'));
    }

    /**
     * 提现
     * 每个月20日为结算日，21日0点之后，访问钱包可激活结算，然后需要医脉后台进行报税
     * 报税之后可以提现，提现申请后，将所有可提现金额全部可以提现。
     *
     * @param IdRequest $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function withdraw(IdRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $bankId = $request['id'];

        try {
            SettlementRecord::where('doctor_id', $user->id)
                ->where('status', 1)//结算状态； 0：未缴税；1：已完成结算，可提现
                ->where('withdraw_status', 0)//提现状态；0为未提现，1为申请提现，9为成功
                ->update([
                    'withdraw_status' => 1,
                    'withdraw_request_date' => date('Y-m-d H:i:s', time()),
                    'withdraw_bank_no' => $bankId
                ]);

            return response()->json(['success' => ''], 204);
        } catch (\Exception $e) {
            return response()->json(['message' => '入库失败'], 500);
        }
    }

    /**
     * 结算上月
     *
     * @param $id
     */
    public function checkout($id)
    {
        /**
         * 年月信息：
         * 需判断是否为20号以后，20号为上月结算日
         */
        $year = date('Y'); //当年
        if (date('d') > 20) {
            $month = date('m') - 1; //上个月
            if ($month == 0) { //如果本月是一月，则重置年月
                $month = 12;
                $year = $year - 1;
            }
        } else {
            $month = date('m') - 2; //上上个月
            if ($month == -1) { //如果本月是一月，则重置年月
                $month = 11;
                $year = $year - 1;
            } elseif ($month == 0) { //如果本月是二月，则重置年月
                $month = 12;
                $year = $year - 1;
            }
        }

        /**
         *
         */
        $settlement = SettlementRecord::where('doctor_id', $id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        if (!isset($settlement->id)) {
            $totals = AppointmentFee::sumTotal($id);
            foreach ($totals as $total) {
                if ($total->year == $year && $total->month == $month) {
                    $data = [
                        'doctor_id' => $id,
                        'total' => $total->total / 100, //单位是分
                        'year' => $year,
                        'month' => $month,
                        'status' => 0 //结算状态； 0：未缴税；1：已完成结算，可提现
                    ];

                    SettlementRecord::create($data);

                    break;
                }
            }

            /**
             * 修改已经进行提现的流程的数据状态
             */
//            $pendingIdList = Order::allPending($id, $year, $month);
//            Order::whereIn('id', $pendingIdList)
//                ->update(['settlement_status' => '待结算']); //settlement_status：结算状态:待结算、可提现

            $pendingIdList_fee = AppointmentFee::allPending($id, $year, $month);
            AppointmentFee::whereIn('id', $pendingIdList_fee)
                ->update(['settlement_status' => '待结算']); //settlement_status：结算状态:待结算、可提现
        }
    }
}
