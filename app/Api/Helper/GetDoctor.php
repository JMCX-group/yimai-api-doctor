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
    private static $url = 'http://121.41.86.156/api.php';

    //参数中的ID和KEY
    private static $auth_id = 10021;
    private static $auth_key = '59e30c25d56cad3961b1318e765e0f18';

    //Post配置
    private static $timeout = 10;

    /**
     * CURL POST 请求
     *
     * @param $data
     * @return mixed
     */
    public static function curlPostContents($data)
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, self::$url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, self::$timeout);
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
     * @param $phoneList //多个用','分隔。不能有空格，如 '13738409853,13824912175'
     * @return string
     */
    public static function getDoctor($phoneList)
    {
        $data = array(
            'auth_id' => self::$auth_id,
            'auth_key' => self::$auth_key,
            'mobile' => $phoneList
        );

        return self::formatDoctor(msgpack_unpack(trim(self::curlPostContents($data))));
    }

    /**
     * 格式化信息。
     *
     * @param $data
     * @return array|bool
     */
    public static function formatDoctor($data)
    {
        return $data;
        if (isset($data['auth']['status']) && $data['auth']['status'] == 'true') {
            $allData = $data['list'];
            $tmpData = array();
            foreach ($allData as $item) {
                if ($item != '') {
                    array_push($tmpData, $item);
                }
            }
            if (count($tmpData) == 0) {
                $newData = false;
            } else {
                $newData = $tmpData;
            }
        } else {
            $newData = false;
        }

        return $newData;
    }
}
