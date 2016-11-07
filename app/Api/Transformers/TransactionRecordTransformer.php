<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\TransactionRecord;
use League\Fractal\TransformerAbstract;

class TransactionRecordTransformer extends TransformerAbstract
{
    public function transform(TransactionRecord $record)
    {
        return [
            'name' => $record['name'],
            'price' => $record['price'],
            'type' => $record['type'],
            'status' => $record['status'],
            'time' => $record['created_at']
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
            'name' => $record['name'],
            'price' => $record['price'],
            'type' => $record['type'],
            'status' => $record['status'],
            'time' => $record['created_at']->format('Y-m-d H:i:s')
        ];
    }
}
