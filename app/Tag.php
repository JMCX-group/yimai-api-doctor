<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'dept_id',
        'dept_lv2_id',
        'name',
        'hot',
        'status'
    ];

    /**
     * 获取2级科室的标签
     *
     * @param $deptLv1Id
     * @return mixed
     */
    public static function getDeptLv2Tags($deptLv1Id)
    {
        return DB::select(
            "select tags.*,dept_standards.name as dept_name from tags left join dept_standards on tags.dept_lv2_id=dept_standards.id where tags.dept_id=$deptLv1Id;"
        );

    }
}
