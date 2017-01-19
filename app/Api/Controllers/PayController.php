<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Helper\MsgAndNotification;
use App\Api\Helper\WeiXinPay;
use App\Appointment;
use App\Doctor;
use App\Patient;
use Illuminate\Http\Request;
use App\Order;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{
    /**
     * @var WeiXinPay
     */
    private $wxPay;

    /**
     * PaymentController constructor.
     */
    public function __construct()
    {
        $this->wxPay = new WeiXinPay();
    }

    /**
     * Debug
     *
     * @param $content
     */
    public function writeFile($content)
    {
        $fileName = "test-pay/pay.file";

        //以读写方式打写指定文件，如果文件不存则创建
        if (($TxtRes = fopen($fileName, "w+")) === FALSE) {
            exit();
        }

        if (!fwrite($TxtRes, $content)) { //将信息写入文件
            fclose($TxtRes);
            exit();
        }
        fclose($TxtRes); //关闭指针
    }

    /**
     * 微信支付回調函數
     */
    public function notifyUrl()
    {
        $wxData = (array)simplexml_load_string(file_get_contents('php://input'), 'SimpleXMLElement', LIBXML_NOCDATA);
        Log::info('appointment-pay', ['context' => json_encode($wxData)]); //测试期间
        if ($wxData['return_code'] == 'SUCCESS' && $wxData['result_code'] == 'SUCCESS') {
            $this->paymentProcessing($wxData);
            echo 'SUCCESS';
        } else {
            echo 'FAIL';
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function wxPayOrderQuery(Request $request)
    {
        $wxData = $this->wxPay->wxOrderQuery($request['id']);
        if ($wxData['return_code'] == 'SUCCESS' && $wxData['trade_state'] == 'SUCCESS') {
            $data = $this->paymentProcessing($wxData);
        } else {
            $data = ['result' => 'fail'];
        }

        return response()->json(compact('data'));
    }

    /**
     * 批量处理一些订单
     *
     * @param $idArr
     */
    public function batProcessing($idArr)
    {
        foreach ($idArr as $item) {
            $wxData = $this->wxPay->wxOrderQuery($item->id);
            if ($wxData['return_code'] == 'SUCCESS' && $wxData['result_code'] == 'SUCCESS' && $wxData['trade_state'] == 'SUCCESS') {
                $this->paymentProcessing($wxData);
            } else {
                Log::info('wx-order-query-error', ['context' => json_encode($wxData)]);
                if($wxData['trade_state'] == 'NOTPAY'){
                    $this->notPayProcessing($wxData);
                }
            }
        }
    }

    /**
     * 处理未支付的
     *
     * @param $wxData
     */
    public function notPayProcessing($wxData)
    {
        $outTradeNo = $wxData['out_trade_no'];
        $order = Order::where('out_trade_no', $outTradeNo)->first();
        if (!empty($order->id)) {
            $order->ret_data = json_encode($wxData);
            $order->save();

            $appointment = Appointment::find($outTradeNo);
            if (!empty($appointment->id)) {
                if ($appointment->status == 'wait-1') {
                    $appointment->is_pay = '0';
                    $appointment->save();
                }
            }
        }
    }

    /**
     * 统一处理
     *
     * @param $wxData
     * @return array
     */
    public function paymentProcessing($wxData)
    {
        $outTradeNo = $wxData['out_trade_no'];

        $order = Order::where('out_trade_no', $outTradeNo)->first();
        if (!empty($order->id)) {
            $order->status = 'end';
            $order->time_expire = $wxData['time_end'];
            $order->ret_data = json_encode($wxData);
            $order->save();

            $appointment = Appointment::find($outTradeNo);
            if ($appointment->status == 'wait-1') {
                $appointment->is_pay = '1';
                $appointment->status = 'wait-2';
                $appointment->transaction_id = $wxData['transaction_id'];
                $appointment->save();

                MsgAndNotification::sendAppointmentsMsg($appointment); //推送消息记录

                $doctor = Doctor::where('id', $appointment['doctor_id'])->first();
                if (isset($doctor->id) && ($doctor->device_token != '' && $doctor->device_token != null)) {
                    MsgAndNotification::pushAppointmentMsg($doctor->device_token, $appointment->status, $appointment->id, 'doctor'); //向医生端推送消息
                }
            }

            $data = ['result' => 'success'];
        } else {
            $data = ['result' => 'fail', 'debug' => '木有订单信息啊'];
        }

        return $data;
    }
}
