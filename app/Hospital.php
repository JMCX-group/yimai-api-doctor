<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * Class Hospital
 * @package App
 */
class Hospital extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hospitals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['province_id', 'city_id', 'name', 'three_a', 'top_dept_num', 'status'];

    /**
     * 按省市搜索医院
     *
     * @param $field
     * @param $provinceId
     * @param $cityId
     * @return mixed
     */
    public static function searchHospital_provinces($field, $provinceId, $cityId)
    {
        if ($cityId) {
            return Hospital::select('hospitals.*', 'provinces.name AS province', 'citys.name AS city')
                ->leftJoin('provinces', 'provinces.id', '=', 'hospitals.province_id')
                ->leftJoin('citys', 'citys.id', '=', 'hospitals.city_id')
                ->where('hospitals.city_id', $cityId)
                ->where('hospitals.name', 'LIKE', '%'.$field.'%')
                ->orderBy('hospitals.three_a', 'desc')
                ->get();
        } elseif ($provinceId) {
            return Hospital::select('hospitals.*', 'provinces.name AS province', 'citys.name AS city')
                ->leftJoin('provinces', 'provinces.id', '=', 'hospitals.province_id')
                ->leftJoin('citys', 'citys.id', '=', 'hospitals.city_id')
                ->where('hospitals.province_id', $provinceId)
                ->where('hospitals.name', 'LIKE', '%'.$field.'%')
                ->orderBy('hospitals.three_a', 'desc')
                ->get();
        } else {
            return Hospital::select('hospitals.*', 'provinces.name AS province', 'citys.name AS city')
                ->leftJoin('provinces', 'provinces.id', '=', 'hospitals.province_id')
                ->leftJoin('citys', 'citys.id', '=', 'hospitals.city_id')
                ->where('hospitals.name', 'LIKE', '%'.$field.'%')
                ->orderBy('hospitals.three_a', 'desc')
                ->get();
        }
    }
}
