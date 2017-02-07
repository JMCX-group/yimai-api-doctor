<?php

namespace App\Console;

use App\Api\Helper\MsgAndNotification;
use App\Api\Helper\WeiXinPay;
use App\Appointment;
use App\PatientRechargeRecord;
use App\PatientWallet;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
         * 每分钟刷新订单信息：
         */
        $schedule->call(function () {
            self::updateExpiredAndPushAppointment();
//            self::processOrders(); //已经不需要了，使用appointment fee管理支付了
        })->everyMinute();

        /**
         * 每10分钟刷新支付信息：
         */
        $schedule->call(function () {
            self::updateWeChatPayInfo();
        })->everyTenMinutes();
    }

    /**
     * 更新微信支付信息
     */
    public static function updateWeChatPayInfo()
    {
        $wxPay = new WeiXinPay();
        $errPayList = PatientRechargeRecord::getErrList();
        foreach ($errPayList as $item) {
            $wxData = $wxPay->wxOrderQuery($item->out_trade_no);
            Log::info('auto-wechat-query', ['context' => json_encode($wxData)]); //测试期间
            if ($wxData['return_code'] == 'SUCCESS' && $wxData['trade_state'] == 'SUCCESS') {
                self::rechargeSuccess($wxData);
            } else {
                self::rechargeFailed($wxData);
            }
        }
    }

    /**
     * 充值失败
     *
     * @param $wxData
     */
    public static function rechargeFailed($wxData)
    {
        $outTradeNo = $wxData['out_trade_no'];

        try {
            /**
             * 订单状态更新
             */
            $rechargeRecord = PatientRechargeRecord::where('out_trade_no', $outTradeNo)->first();
            if (!empty($rechargeRecord->id)) {
                $rechargeRecord->status = 'err';
                $rechargeRecord->time_expire = $wxData['time_end'];
                $rechargeRecord->ret_data = json_encode($wxData);
                $rechargeRecord->save();
            }
        } catch (\Exception $e) {
            Log::info('auto-wechat-query-recharge-failed', ['context' => $e->getMessage()]);
        }
    }

    /**
     * 充值成功
     *
     * @param $wxData
     */
    public static function rechargeSuccess($wxData)
    {
        $outTradeNo = $wxData['out_trade_no'];

        try {
            /**
             * 订单状态更新
             */
            $rechargeRecord = PatientRechargeRecord::where('out_trade_no', $outTradeNo)->first();
            if (!empty($rechargeRecord->id)) {
                $rechargeRecord->status = 'end';
                $rechargeRecord->time_expire = $wxData['time_end'];
                $rechargeRecord->ret_data = json_encode($wxData);
                $rechargeRecord->save();

                /**
                 * 钱包信息更新
                 */
                $wallet = PatientWallet::where('patient_id', $rechargeRecord->patient_id)->first();
                if (!isset($wallet->patient_id)) {
                    $wallet = new PatientWallet();
                    $wallet->patient_id = $rechargeRecord->patient_id;
                    $wallet->total = 0;
                }
                $wallet->total += ($wxData['total_fee'] / 100);
                $wallet->total = $wallet->total * 10000; //TODO 测试期间乘以10000倍
                $wallet->save();
            }
        } catch (\Exception $e) {
            Log::info('auto-wechat-query-recharge-success', ['context' => $e->getMessage()]);
        }
    }

    /**
     * 更新并推送过期信息
     */
    public static function updateExpiredAndPushAppointment()
    {
        /**
         * 更新过期（24小时）未处理的信息并推送：
         * wait-0 to close-2
         */
        $wait0Appointments = Appointment::getOverdueAcceptedList();
        MsgAndNotification::sendAppointmentsMsg_list($wait0Appointments, 'close-2');

        /**
         * 更新过期（12小时）未支付的信息并推送：
         * wait-1 to close-1
         */
        $wait1Appointments = Appointment::getOverduePaymentList();
        MsgAndNotification::sendAppointmentsMsg_list($wait1Appointments, 'close-1');

        /**
         * 更新过期（48小时）未接诊的信息并推送：
         * wait-2 to close-2
         */
        $wait2Appointments = Appointment::getOverdueNotAdmissionsList();
        MsgAndNotification::sendAppointmentsMsg_list($wait2Appointments, 'close-2');

        /**
         * 更新过期（4小时）未确认完成面诊的信息并推送：
         * wait-3 to completed-1
         */
        $wait2Appointments = Appointment::getOverdueNotConfirmedFace('wait-3');
        MsgAndNotification::sendAppointmentsMsg_list($wait2Appointments, 'completed-1', true);

        /**
         * 更新过期（到面诊时间）未确认医生改期的信息并推送：
         * wait-4 to cancel-5
         */
        $wait2Appointments = Appointment::getOverdueNotConfirmedRescheduled();
        MsgAndNotification::sendAppointmentsMsg_list($wait2Appointments, 'cancel-5', true);

        /**
         * 更新过期（4小时）未确认完成面诊的信息并推送：
         * wait-5 to completed-2
         */
        $wait2Appointments = Appointment::getOverdueNotConfirmedFace('wait-5');
        MsgAndNotification::sendAppointmentsMsg_list($wait2Appointments, 'completed-2', true);
    }

    /**
     * 处理已支付没有回调处理的订单
     */
//    public static function processOrders()
//    {
//        $needProcessAppointments = Appointment::getPaidNoCallbackList();
//        if (count($needProcessAppointments) > 0) {
//            $payCtrl = new PayController();
//            $payCtrl->batProcessing($needProcessAppointments);
//        }
//    }
}
