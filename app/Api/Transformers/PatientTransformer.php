<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\Patient;
use League\Fractal\TransformerAbstract;

class PatientTransformer extends TransformerAbstract
{
    public function transform(Patient $patient)
    {
        return [
            'id' => $patient['id'],
            'phone' => $patient['phone'],
            'name' => $patient['name'],
            'sex' => $patient['gender'],
            'age' => $patient['birthday'],
        ];
    }
}
