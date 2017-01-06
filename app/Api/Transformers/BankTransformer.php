<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\DoctorBank;
use League\Fractal\TransformerAbstract;

class BankTransformer extends TransformerAbstract
{
    /**
     * @param DoctorBank $bank
     * @return array
     */
    public function transform(DoctorBank $bank)
    {
        return [
            'id' => $bank['id'],
            'name' => $bank['bank_name'],
            'info' => $bank['bank_info'],
            'no' => $bank['bank_no'],
            'verify' => $bank['real_name_verify'],
            'status' => $bank['status'],
            'desc' => $bank['desc']
        ];
    }
}
