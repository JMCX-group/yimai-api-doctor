<?php
/**
 * Created by PhpStorm.
 * User: liyingxuan
 * Date: 2016/8/16
 * Time: 19:52
 */

namespace App\Api\Helper;

class WeiXinPay
{
    public $parameters;
    private $url;
    private $key;
    private $appId;
    private $mchId;
    private $notifyUrl;

    /**
     * Init.
     *
     * WeiXinPay constructor.
     */
    public function __construct()
    {
        $this->url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $this->key = '555d4062c619451b884267c46ab85ca3';
        $this->appId = 'wx2097e8b109f9dc35';
        $this->mchId = '1273535201';
        $this->notifyUrl = 'http://139.129.167.9/api/pay/notify_url';

        $this->orderQueryUrl = 'https://api.mch.weixin.qq.com/pay/orderquery';
    }

    /**
     * WeChat Pay
     *
     * @param $outTradeNo
     * @param $body
     * @param $totalFee
     * @param $timeExpire
     * @return array
     */
    public function wxPay($outTradeNo, $body, $totalFee, $timeExpire)
    {
        /**
         * 参数组:
         */
        $data = array(
            'appid' => $this->appId,
            'attach' => 'weixinpay',
            'body' => $body,
            'mch_id' => $this->mchId,
            'nonce_str' => $this->random('15'),
            'notify_url' => $this->notifyUrl,
            'out_trade_no' => $outTradeNo,
            'spbill_create_ip' => $this->getIp(),
            'time_expire' => $timeExpire,
            'total_fee' => $totalFee,
            'trade_type' => 'APP'
        );
        $data['sign'] = $this->wxMd5Sign($data);
        $dataXml = $this->wxArrayToXml($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30000);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataXml);
        $retData = curl_exec($ch);
        curl_close($ch);

        return $this->wxRetAppData($retData);
    }

    /**
     * @param $outTradeNo
     * @return array
     */
    public function wxOrderQuery($outTradeNo)
    {
        /**
         * 参数组:
         */
        $data = array(
            'appid' => $this->appId,
            'mch_id' => $this->mchId,
            'nonce_str' => $this->random('32'),
            'out_trade_no' => $outTradeNo
        );
        $data['sign'] = $this->wxMd5Sign($data);
        $dataXml = $this->wxArrayToXml($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30000);
        curl_setopt($ch, CURLOPT_URL, $this->orderQueryUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataXml);
        $retData = curl_exec($ch);
        curl_close($ch);

        return $wxData = (array)simplexml_load_string($retData, 'SimpleXMLElement', LIBXML_NOCDATA);
    }

    /**
     * Ret app data.
     *
     * @param $wxData
     * @return array
     */
    public function wxRetAppData($wxData)
    {
        $wxData = (array)simplexml_load_string($wxData, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!empty($wxData)) {
            if ($wxData['return_code'] == 'SUCCESS') {
                $data['appid'] = $wxData['appid'];
                $data['noncestr'] = $wxData['nonce_str'];
                $data['package'] = 'Sign=WXPay';
                $data['partnerid'] = $wxData['mch_id'];
                $data['prepayid'] = $wxData['prepay_id'];
                $data['timestamp'] = time();
                $data['sign'] = $this->wxMd5Sign($data);

                return $data;
            }
            return $data = ['message' => 'false'];
        }
        return $data = ['message' => 'false'];
    }

    /**
     * MD5
     *
     * @param $data
     * @return string
     */
    public function wxMd5Sign($data)
    {
        $content = '';
        foreach ($data as $key => $value) {
            if ($content != '') {
                $content .= '&' . $key . '=' . $value;
            } else {
                $content = $key . '=' . $value;
            }
        }

        try {
            if (is_null($content)) {
                throw new \Exception('财付通签名内容不能为空');
            }
            $signStr = $content . '&key=' . $this->key;

            return strtoupper(md5($signStr));
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Array to xml.
     *
     * @param null $parameters
     * @return string
     */
    public function wxArrayToXml($parameters = NULL)
    {
        if (is_null($parameters)) {
            $parameters = $this->parameters;
        }

        if (!is_array($parameters) || empty($parameters)) {
            die('参数不为数组无法解析');
        }

        $xml = "<xml>";
        foreach ($parameters as $key => $val) {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";

        return $xml;
    }

    /**
     * Random.
     *
     * @param $length
     * @param int $numeric
     * @return string
     */
    public function random($length, $numeric = 0)
    {
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }

        return $hash;
    }

    /**
     * Get ip.
     *
     * @return mixed
     */
    public function getIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        return ($ip == '::1') ? '127.0.0.1' : $ip;
    }
}
