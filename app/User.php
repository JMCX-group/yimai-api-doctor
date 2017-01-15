<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use DB;

/**
 * Class User
 * @package App
 */
class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'doctors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dp_code',
        'phone',
        'email',
        'password',
        'rong_yun_token',
        'device_token',
        'name',
        'avatar',
        'gender',
        'province_id',
        'city_id',
        'hospital_id',
        'dept_id',
        'title',
        'college_id',
        'id_num',
        'tag_list',
        'profile',
        'auth',
        'auth_img',
        'qr_code_url',
        'fee_switch',
        'fee',
        'fee_face_to_face',
        'admission_set_fixed',
        'admission_set_flexible',
        'verify_switch',
        'friends_friends_appointment_switch',
        'application_card',
        'address',
        'addressee',
        'receive_phone',
        'blacklist',
        'inviter_dp_code',
        'role',
        'remember_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get logged user info.
     *
     * @return mixed
     */
    public static function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent'], $e->getStatusCode());
        }

        // the token is valid and we have found the user via the sub claim
        return $user;
    }

    /**
     * Generate new DP Code.
     * 科室编号3位 + 301开始的编码.
     * 300以内为内定.
     *
     * @param $deptId
     * @return mixed
     */
    public static function generateDpCode($deptId)
    {
        $data = User::select('dp_code')
            ->where('dept_id', $deptId)
            ->orderBy('dp_code', 'desc')
            ->first();

        if (isset($data->dp_code)) {
            return intval($data->dp_code) + 1;
        } else {
            return 301;
        }
    }

    /**
     * Get DP Code.
     *
     * @param $id
     * @return string
     */
    public static function getDpCode($id)
    {
        $data = User::select('dp_code', 'dept_id')->where('id', $id)->first();

        $dpCode = str_pad($data->dept_id, 3, '0', STR_PAD_LEFT) . $data->dp_code;

        return $dpCode;
    }

    /**
     * Get inviter name.
     *
     * @param $dpCode
     * @return bool
     */
    public static function getInviter($dpCode)
    {
        $data = User::select('name')
//            ->where('city_id', City::select('id')->where('code', substr($dpCode, 0, 3))->get()->first()->id)
            ->where('dept_id', substr($dpCode, 0, 3))
            ->where('dp_code', substr($dpCode, 3))
            ->get();

        if (isset($data->first()->name)) {
            return $data->first()->name;
        } else {
            return false;
        }
    }

    /**
     * @param $dpCode
     * @return bool
     */
    public static function getDoctorForDpCode($dpCode)
    {
        return User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth',
            'doctors.province_id', 'doctors.city_id', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.college_id',
            'doctors.tag_list', 'doctors.profile',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->where('dept_id', substr($dpCode, 0, 3))
            ->where('dp_code', substr($dpCode, 3))
            ->get()
            ->first();
    }

    /**
     * Get same type contact count.
     *
     * @param $hospitalId
     * @param $deptId
     * @param $collegeId
     * @return array
     */
    public static function getSameTypeContactCount($hospitalId, $deptId, $collegeId)
    {
        $hospitalCount = User::where('hospital_id', $hospitalId)->count();
        $deptCount = User::where('dept_id', $deptId)->count();
        $collegeCount = ($collegeId == null || $collegeId == '') ? 0 : User::where('college_id', $collegeId)->count();

        return [
            'hospital' => $hospitalCount,
            'department' => $deptCount,
            'college' => $collegeCount,
        ];
    }

    /**
     * 根据必填的字段值和可选的城市/医院/科室条件搜索符合条件的医生.
     * id转name.
     * 按是否三甲医院排序.
     *
     * @param $myId
     * @param $field
     * @param $cityId
     * @param $hospitalId
     * @param $deptId
     * @return mixed
     */
    public static function searchDoctor($myId, $field, $cityId, $hospitalId, $deptId)
    {
        $condition = "where ";
        $condition .= $cityId ? "doctors.city_id = '$cityId' " : "";
        $condition .= $cityId ? "and " : "";
        $condition .= $hospitalId ? "doctors.hospital_id = '$hospitalId' " : "";
        $condition .= $hospitalId ? "and " : "";
        $condition .= $deptId ? "doctors.dept_id = '$deptId' " : "";
        $condition .= $deptId ? "and " : "";
        $condition .= " (";
        $condition .= "doctors.name like '%$field%' ";
        $condition .= $hospitalId ? "" : "or hospitals.name like '%$field%' ";
        $condition .= $deptId ? "" : "or dept_standards.name like '%$field%' ";
        $condition .= "or doctors.tag_list like '%$field%' ";
        $condition .= ") ";
        $condition .= "and doctors.id!=$myId ";

        return self::defaultSearchSql($condition, "ORDER BY hospitals.three_a desc");
    }

    /**
     * 搜索同城市的医生信息.
     * id转name.
     *
     * @param $myId
     * @param $field
     * @param $cityId
     * @return mixed
     */
    public static function searchDoctor_admissions($myId, $field, $cityId)
    {
        $condition = "where `doctors`.`city_id` = '$cityId' ";
        $condition .= $field ? "and (doctors.name like '%$field%' ) " : "";
        $condition .= "and doctors.id!=$myId ";

        return self::defaultSearchSql($condition);
    }

    /**
     * 搜索同医院的医生信息.
     *
     * @param $myId
     * @param $field
     * @param $hospitalId
     * @param $cityId
     * @param $deptId
     * @return mixed
     */
    public static function searchDoctor_sameHospital($myId, $field, $hospitalId, $cityId, $deptId)
    {
        $condition = "where ";
        $condition .= $cityId ? "doctors.city_id = '$cityId' " : "";
        $condition .= $cityId ? "and " : "";
        $condition .= $deptId ? "doctors.dept_id = '$deptId' " : "";
        $condition .= $deptId ? "and " : "";
        $condition .= "doctors.hospital_id = '$hospitalId' ";
        $condition .= $field ? "and (doctors.name like '%$field%' or dept_standards.name like '%$field%' ) " : "";
        $condition .= "and doctors.id!=$myId ";

        return self::defaultSearchSql($condition);
    }

    /**
     * 搜索相同科室的所有医生信息。
     *
     * @param $myId
     * @param $field
     * @param $deptId
     * @param $cityId
     * @param $hospitalId
     * @return mixed
     */
    public static function searchDoctor_sameDept($myId, $field, $deptId, $cityId, $hospitalId)
    {
        $condition = "where ";
        $condition .= $cityId ? "doctors.city_id = '$cityId' " : "";
        $condition .= $cityId ? "and " : "";
        $condition .= $hospitalId ? "doctors.hospital_id = '$hospitalId' " : "";
        $condition .= $hospitalId ? "and " : "";
        $condition .= "doctors.dept_id = '$deptId' ";
        $condition .= $field ? "and (doctors.name like '%$field%' or hospitals.name like '%$field%' or doctors.tag_list like '%$field%') " : "";
        $condition .= "and doctors.id!=$myId ";

        return self::defaultSearchSql($condition);
    }

    /**
     * 搜索同院校下的医生信息。
     *
     * @param $myId
     * @param $field
     * @param $collegeId
     * @param $cityId
     * @param $hospitalId
     * @param $deptId
     * @return mixed
     */
    public static function searchDoctor_sameCollege($myId, $field, $collegeId, $cityId, $hospitalId, $deptId)
    {
        $condition = "where ";
        $condition .= $cityId ? "doctors.city_id = '$cityId' " : "";
        $condition .= $cityId ? "and " : "";
        $condition .= $hospitalId ? "doctors.hospital_id = '$hospitalId' " : "";
        $condition .= $hospitalId ? "and " : "";
        $condition .= $deptId ? "doctors.dept_id = '$deptId' " : "";
        $condition .= $deptId ? "and " : "";
        $condition .= "doctors.college_id = '$collegeId' ";
        $condition .= $field
            ? "and (doctors.name like '%$field%' or dept_standards.name like '%$field%' " .
            "or hospitals.name like '%$field%' or doctors.tag_list like '%$field%') "
            : "";
        $condition .= "and doctors.id!=$myId ";

        return self::defaultSearchSql($condition);
    }

    /**
     * 标准的查询SQL语句
     *
     * @param $condition
     * @param $order
     * @return mixed
     */
    public static function defaultSearchSql($condition, $order = '')
    {
        return DB::select(
            "SELECT doctors.id, doctors.name, doctors.avatar, " .
            "doctors.province_id, doctors.city_id, doctors.hospital_id, doctors.dept_id, doctors.title, " .
            "doctors.admission_set_fixed, doctors.admission_set_flexible, " .
            "provinces.name AS province, citys.name AS city, hospitals.name AS hospital, dept_standards.name AS dept " .
            "FROM doctors " .
            "LEFT JOIN provinces ON provinces.id=doctors.province_id " .
            "LEFT JOIN dept_standards ON dept_standards.id=doctors.dept_id " .
            "LEFT JOIN citys ON citys.id=doctors.city_id " .
            "LEFT JOIN hospitals ON hospitals.id=doctors.hospital_id " .
            $condition .
            $order
        );
    }

    /**
     * 获得某个医生主页信息
     *
     * @param $id
     * @return mixed
     */
    public static function findDoctor($id)
    {
        return User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth',
            'doctors.province_id', 'doctors.city_id', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.college_id',
            'doctors.tag_list', 'doctors.profile',
            'doctors.verify_switch', 'doctors.friends_friends_appointment_switch',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->where('doctors.id', $id)
            ->get()
            ->first();
    }

    /**
     * 获得某个医生全部可视化信息
     *
     * @param $id
     * @return mixed
     */
    public static function getDoctorAllInfo($id)
    {
        return User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth',
            'doctors.province_id', 'doctors.city_id', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.college_id',
            'doctors.tag_list', 'doctors.profile',
            'doctors.fee',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->where('doctors.id', $id)
            ->get()
            ->first();
    }

    /**
     * 通过手机号获取医生信息
     *
     * @param $phone
     * @return mixed
     */
    public static function findDoctor_byPhone($phone)
    {
        return User::select(
            'doctors.id', 'doctors.name', 'doctors.avatar', 'doctors.title', 'doctors.auth',
            'doctors.province_id', 'doctors.city_id', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.college_id',
            'doctors.tag_list', 'doctors.profile',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->where('doctors.phone', $phone)
            ->get()
            ->first();
    }
}
