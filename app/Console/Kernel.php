<?php

namespace App\Console;

use App\Api\Helper\MsgAndNotification;
use App\Appointment;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $schedule->call(function () {
            self::updateExpiredAndPushAppointment();
//            self::processOrders(); //已经不需要了，使用appointment fee管理支付了
        })->everyMinute();
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
