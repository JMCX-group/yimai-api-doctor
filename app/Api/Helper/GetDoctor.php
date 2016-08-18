<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

/**
 * 和第三方医生数据库交叉对比医生数据。
 *
 * Class GetDoctor
 * @package App\Api\Helper
 */
class GetDoctor
{
    //Post请求的URL
    private $url = 'http://121.41.86.156/api.php';

    //参数中的ID和KEY
    private $auth_id = 10021;
    private $auth_key = '59e30c25d56cad3961b1318e765e0f18';

    //Post配置
    private $timeout = 10;

    /**
     * CURL POST 请求
     *
     * @param $data
     * @return mixed
     */
    public function curl_post_contents($data)
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curlHandle);
        curl_close($curlHandle);

        return $result;
    }

    /**
     * 获取医生信息
     * 返回信息有可能被msg pack打包过,需要解包,代码在最后一行注释里。
     *
     * @param $phoneList
     * @return string
     */
    function get_doctor($phoneList)
    {
        $data = array(
            'auth_id' => $this->auth_id,
            'auth_key' => $this->auth_key,
            'mobile' => $phoneList  //多个用','分隔。不能有空格，如 '13738409853,13824912175'
        );

        return trim(curl_post_contents($data));
//        return msgpack_pack(trim(curl_post_contents("http://121.41.86.156/api.php", $data)));
    }
}
