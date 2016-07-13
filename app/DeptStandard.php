<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeptStandard extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "dept_standards";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'name'];

    /**
     * 获取某科室所属一级科室下的所有科室ID
     * 
     * @param $deptId
     * @return mixed
     */
    public static function getSameFirstLevelDeptIdList($deptId)
    {
        $dept = DeptStandard::find($deptId);
        if ($dept->parent_id == 0) {
            $deptIdList = DeptStandard::where('parent_id', $deptId)
                ->orWhere('id', $deptId)
                ->lists('id')
                ->toArray();
        } else {
            $deptIdList = DeptStandard::where('parent_id', $dept->parent_id)
                ->orWhere('id', $dept->parent_id)
                ->lists('id')
                ->toArray();
        }
        
        return $deptIdList;
    }
}
