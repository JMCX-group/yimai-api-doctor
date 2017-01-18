<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

/**
 * 约诊状态翻译
 *
 * Class GetDoctor
 * @package App\Api\Helper
 */
class AppointmentStatus
{
    /**
     * 约诊状态文案
     *
     * @param $status
     * @return string
     */
    public static function content($status)
    {

        switch ($status) {
            case 'wait-0':
                $retData = '待代约医生确认';
                break;
            case 'wait-1':
                $retData = '待患者付款';
                break;
            case 'wait-2':
                $retData = '患者已付款，待医生确认';
                break;
            case 'wait-3':
                $retData = '医生确认接诊，待面诊';
                break;
            case 'wait-4':
                $retData = '医生改期，待患者确认';
                break;
            case 'wait-5':
                $retData = '患者确认改期，待面诊';
                break;

            case 'close-1':
                $retData = '待患者付款';
                break;
            case 'close-2':
                $retData = '医生过期未接诊,约诊关闭';
                break;
            case 'close-3':
                $retData = '医生拒绝接诊';
                break;

            case 'cancel-1':
                $retData = '患者取消约诊; 未付款';
                break;
            case 'cancel-2':
                $retData = '医生取消约诊';
                break;
            case 'cancel-3':
                $retData = '患者取消约诊; 已付款后';
                break;
            case 'cancel-4':
                $retData = '医生改期之后,医生取消约诊';
                break;
            case 'cancel-5':
                $retData = '医生改期之后,患者取消约诊';
                break;
            case 'cancel-6':
                $retData = '医生改期之后,患者确认之后,患者取消约诊';
                break;
            case 'cancel-7':
                $retData = '医生改期之后,患者确认之后,医生取消约诊';
                break;

            case 'completed-1':
                $retData = '已完成';
                break;
            case 'completed-2':
                $retData = '改期后完成';
                break;

            default:
                $retData = '';
                break;
        }

        return $retData;
    }

    /**
     * 约诊推送文案
     *
     * @param $status
     * @return string
     */
    public static function pushContent($status)
    {

        switch ($status) {
            case 'wait-0':
                $retData = '有患者请求您代约';
                break;
            case 'wait-1':
                $retData = '您有新的约诊订单需要支付';
                break;
            case 'wait-2':
                $retData = '患者已付款，待医生确认';
                break;
            case 'wait-3':
                $retData = '医生确认接诊，待面诊';
                break;
            case 'wait-4':
                $retData = '医生改期，待患者确认';
                break;
            case 'wait-5':
                $retData = '患者确认改期，待面诊';
                break;

            case 'close-1':
                $retData = '待患者付款';
                break;
            case 'close-2':
                $retData = '医生过期未接诊,约诊关闭';
                break;
            case 'close-3':
                $retData = '医生拒绝接诊';
                break;

            case 'cancel-1':
                $retData = '患者取消约诊; 未付款';
                break;
            case 'cancel-2':
                $retData = '医生取消约诊';
                break;
            case 'cancel-3':
                $retData = '患者取消约诊; 已付款后';
                break;
            case 'cancel-4':
                $retData = '医生改期之后,医生取消约诊';
                break;
            case 'cancel-5':
                $retData = '医生改期之后,患者取消约诊';
                break;
            case 'cancel-6':
                $retData = '医生改期之后,患者确认之后,患者取消约诊';
                break;
            case 'cancel-7':
                $retData = '医生改期之后,患者确认之后,医生取消约诊';
                break;

            case 'completed-1':
                $retData = '已完成';
                break;
            case 'completed-2':
                $retData = '改期后完成';
                break;

            default:
                $retData = '';
                break;
        }

        return $retData;
    }
}
