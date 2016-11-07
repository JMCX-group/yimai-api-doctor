<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\DoctorWallet;
use League\Fractal\TransformerAbstract;

class WalletTransformer extends TransformerAbstract
{
    public function transform(DoctorWallet $wallet)
    {
        return [
            'total' => $wallet['total'],
            'billable' => $wallet['billable'],
            'pending' => $wallet['pending'],
            'refunded' => $wallet['refunded']
        ];
    }
}
