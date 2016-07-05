<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\Contact;
use League\Fractal\TransformerAbstract;

class ContactTransformer extends TransformerAbstract
{
    public function transform(Contact $contact)
    {
        return [
            'id' => $contact['id'],
            'doctor_id' => $contact['doctor_id'],
            'phone' => $contact['phone'],
            'name' => $contact['name'],
            'status' => $contact['status']
        ];
    }
}
