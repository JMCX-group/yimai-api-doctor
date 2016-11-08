<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\Order;
use League\Fractal\TransformerAbstract;

class TransactionRecordTransformer extends TransformerAbstract
{
    public function transform(Order $order)
    {
        return [
            'name' => $order['name'],
            'price' => $order['price'],
            'type' => $order['type'],
            'status' => $order['status'],
            'time' => $order['created_at']
        ];
    }

    /**
     * 返回数据变形
     *
     * @param $record
     * @return array
     */
    public static function transformData($record)
    {
        return [
            'id' => $record['id'],
            'name' => $record['body'],
            'transaction_id' => $record['out_trade_no'],
            'price' => $record['total_fee'] / 100, //单位：分
            'type' => $record['type'],
            'status' => $record['settlement_status'],
            'time' => $record['created_at']->format('Y-m-d H:i:s')
        ];
    }
}
