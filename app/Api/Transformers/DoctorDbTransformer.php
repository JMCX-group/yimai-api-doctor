<?php
/**
 * Created by PhpStorm.
 * User: 乔小柒
 * Date: 2016/9/24
 * Time: 13:54
 */
namespace App\Api\Transformers;

use App\DoctorDb;
use League\Fractal\TransformerAbstract;

class DoctorDbTransformer extends TransformerAbstract
{
    /**
     * 入库变形。
     *
     * @param $doctors
     * @return array
     */
    public static function pushTransform($doctors)
    {
        return [
            'phone' => $doctors['Mobile'],
            'name' => $doctors['Name'],
            'hospital' => $doctors['Hospital_Name'],
            'dept' => $doctors['Department'],
            'profession' => $doctors['Profession'],
            'title' => $doctors['Job_Title'],
            'position' => $doctors['Position'],
            'license_no' => $doctors['License_No'],
            'graduate_school' => $doctors['Graduate_School']
        ];
    }
}