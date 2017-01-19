<?php

namespace App\Console;

use App\Api\Helper\MsgAndNotification;
use App\Appointment;
use App\Cron;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;;

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
        })->everyMinute();
    }

    /**
     * 更新并推送过期信息
     */
    public static function updateExpiredAndPushAppointment()
    {
        /**
         * 更新过期（12小时）未支付的信息并推送：
         */
        $wait1Appointments = Appointment::getOverduePaymentList();
        MsgAndNotification::sendAppointmentsMsg_list($wait1Appointments, 'close-1');

        /**
         * 更新过期（48小时）未接诊的信息并推送：
         */
        $wait2Appointments = Appointment::getOverdueNotAdmissionsList();
        MsgAndNotification::sendAppointmentsMsg_list($wait2Appointments, 'close-2');
    }
}
