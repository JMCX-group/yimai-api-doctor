<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
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
        'inviter_dp_code',
        'role',
        'remember_token'
    ];

    protected $table = 'doctors';

    /**
     * @param $id
     * @return mixed
     */
    public static function getOneDoctor($id)
    {
        return Doctor::select(
            'doctors.*',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->where('doctors.id', $id)
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->first();
    }

    /**
     * 获得全部医生所有信息
     *
     * @return mixed
     */
    public static function getDoctor()
    {
        return Doctor::select(
            'doctors.id', 'doctors.name', 'doctors.phone', 'doctors.gender', 'doctors.title', 'doctors.auth',
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
            ->where('doctors.id', '>', '5')
            ->paginate(15);
    }

    /**
     * 获得医生认证信息
     *
     * @param $authStatus
     * @return mixed
     */
    public static function getVerifyDoctor($authStatus)
    {
        return Doctor::select(
            'doctors.id', 'doctors.name', 'doctors.phone', 'doctors.gender', 'doctors.title', 'doctors.auth',
            'doctors.province_id', 'doctors.city_id', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.college_id',
            'doctors.tag_list', 'doctors.profile',
            'doctors.auth', 'doctors.auth_img',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->where('auth', $authStatus)
            ->where('doctors.id', '>', '5')
            ->paginate(15);
    }

    /**
     * 获取申请名片的信息
     *
     * @param $status
     * @return mixed
     */
    public static function getDoctorCardInfoList($status)
    {
        return Doctor::select(
            'doctors.id', 'doctors.name', 'doctors.phone', 'doctors.gender', 'doctors.title', 'doctors.auth',
            'doctors.province_id', 'doctors.city_id', 'doctors.hospital_id', 'doctors.dept_id', 'doctors.college_id',
            'doctors.tag_list', 'doctors.profile',
            'doctors.auth', 'doctors.auth_img',
            'doctors.address', 'doctors.addressee', 'doctors.receive_phone',
            'provinces.name AS province', 'citys.name AS city',
            'hospitals.name AS hospital', 'dept_standards.name AS dept',
            'colleges.name AS college')
            ->leftJoin('provinces', 'provinces.id', '=', 'doctors.province_id')
            ->leftJoin('citys', 'citys.id', '=', 'doctors.city_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'doctors.hospital_id')
            ->leftJoin('dept_standards', 'dept_standards.id', '=', 'doctors.dept_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'doctors.college_id')
            ->where('application_card', $status)
            ->where('doctors.id', '>', '5')
            ->paginate(15);
    }
}
