<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午3:09
 */

namespace App\Api\Controllers;

/**
 * Class HospitalsController
 * @package App\Api\Controllers
 */
class ApiController extends BaseController
{
    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $http = env('MY_API_HTTP_HEAD', 'http://localhost');

        $api = [
            '统一说明' => [
                '数据格式' => 'JSON',
                'url字段' => 'HTTP请求地址; {}表示在链接后直接跟该数据的ID值即可,例:http://api/hospital/77?token=xx,能获取id为77的医院信息',
                'method字段' => 'GET / POST',
                'form-data字段' => '表单数据',
                'response字段' => [
                    'error字段' => 'Token验证信息错误,表示需要重新登录获取Token; 在HTTP状态码非200时,才会有该字段',
                    'message字段' => '业务信息错误; 在HTTP状态码非200时,才会有该字段',
                    'debug字段' => '只有内测时有,用于传递一些非公开数据或调试信息',
                ],
                '特别说明' => '由于框架原因,Token相关和业务相关的错误返回字段无法保持自定义统一',
                'HTTP状态码速记' => [
                    '释义' => 'HTTP状态码有五个不同的类别:',
                    '1xx' => '临时/信息响应',
                    '2xx' => '成功; 200表示成功获取正确的数据; 204表示执行/通讯成功,但是无返回数据',
                    '3xx' => '重定向',
                    '4xx' => '客户端/请求错误; 需检查url拼接和参数; 在我们这会出现可以提示的[message]或需要重新登录获取token的[error]',
                    '5xx' => '服务器错误; 可以提示服务器崩溃/很忙啦~',
                ]
            ],

            '无需Token验证' => [
                'API文档' => [
                    'url' => $http . '/api',
                    'method' => 'GET'
                ],

                '用户' => [
                    '注册' => [
                        'url' => $http . '/api/user/register',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码',
                            'password' => '6-60位密码',
                            'verify_code' => '4位数字验证码'
                        ],
                        'response' => [
                            'token' => '成功后会返回登录之后的token值',
                            'message' => ''
                        ]
                    ],
                    '发送验证码' => [
                        'url' => $http . '/api/user/verify-code',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码'
                        ],
                        'response' => [
                            'debug' => '为了测试方便,成功后会返回随机的4位手机验证码,正式版上线时没有该项',
                            'message' => ''
                        ]
                    ],
                    '获取邀请人' => [
                        'url' => $http . '/api/user/inviter',
                        'method' => 'POST',
                        'form-data' => [
                            'inviter' => '8位长的纯数字号码'
                        ],
                        'response' => [
                            'name' => '返回正确数字号码对应的用户姓名',
                            'message' => ''
                        ]
                    ],
                    '登录' => [
                        'url' => $http . '/api/user/login',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码',
                            'password' => '6-60位密码'
                        ],
                        'response' => [
                            'token' => '成功后会返回登录之后的token值',
                            'message' => ''
                        ]
                    ],
                    '重置密码' => [
                        'url' => $http . '/api/user/reset-pwd',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码',
                            'password' => '6-60位密码',
                            'verify_code' => '4位数字验证码'
                        ],
                        'response' => [
                            'token' => '成功后会返回登录之后的token值',
                            'message' => ''
                        ]
                    ]
                ],
                '静态资源' => [
                    '说明' => '和图片一样是相对链接，前面拼域名或IP即可访问； 例如：http://101.201.40.220/about/contact-us ，可以访问关于我们',
                    '关于我们' => '/about/contact-us',
                    '医脉简介' => '/about/introduction',
                    '律师信息' => '/about/lawyer'
                ]
            ],

            '需要Token验证' => [

                '初始化信息' => [
                    '启动软件初始化' => [
                        'url' => $http . '/api/init',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'user' => [
                                'id' => '用户id',
                                'code' => '医脉码',
                                'phone' => '用户注册手机号',
                                'email' => '用户邮箱',
                                'rong_yun_token' => '融云token',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'sex' => '性别',
                                'province' => [
                                    'id' => '用户所在省份ID',
                                    'name' => '用户所在省份名称'
                                ],
                                'city' => [
                                    'id' => '用户所在城市ID',
                                    'name' => '用户所在城市名称'
                                ],
                                'hospital' => [
                                    'id' => '用户所在医院ID',
                                    'name' => '用户所在医院名称'
                                ],
                                'department' => [
                                    'id' => '用户所在科室ID',
                                    'name' => '用户所在科室名称'
                                ],
                                'job_title' => '用户职称',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'auth_img' => '认证图片url,相对路径; url用逗号相隔,最多5张;',
                                'fee_switch' => '1:开, 0:关',
                                'fee' => '接诊收费金额',
                                'fee_face_to_face' => '当面咨询收费金额',
                                'admission_set_fixed' => [
                                    '说明' => '接诊时间设置,固定排班; 接收json,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                    '格式案例' => [
                                        'week' => 'sun',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'admission_set_flexible' => [
                                    '说明' => '接诊时间设置,灵活排班; 接收json,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                    '格式案例' => [
                                        'date' => '2016-06-23',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'verify_switch' => '隐私设置: 添加好友验证开关; 默认值为1,即开',
                                'friends_friends_appointment_switch' => '隐私设置: 好友的好友可以向我发起约诊开关; 默认值为0,即关',
                                'inviter' => '邀请者'
                            ],
                            'relations' => [
                                'same' => [
                                    'hospital' => '同医院的人数',
                                    'department' => '同领域的人数',
                                    'college' => '同学校的人数'
                                ],
                                'unread' => '好友信息的未读数量',
                                'count' => [
                                    'doctor' => '我的朋友中共有多少名医生',
                                    'hospital' => '我的朋友中分别属于多少家医院'
                                ],
                                'friends' => [
                                    'id' => '用户ID',
                                    'name' => '用户姓名',
                                    'head_url' => '头像URL',
                                    'hospital' => '所属医院',
                                    'department' => '所属科室',
                                    'job_title' => '职称'
                                ]
                            ],
                            'recent_contacts' => [
                                'id' => '用户id',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'department' => '用户所在科室名称',
                                'is_auth' => '是否认证,1为认证,0为未认证'
                            ],
                            'sys_info' => [
                                'radio_unread_count' => '未读的广播数量',
                                'admissions_unread_count' => '未读的接诊信息数量',
                                'appointment_unread_count' => '未读的约诊信息数量'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '用户信息' => [
                    '查询登陆用户自己的信息' => [
                        'url' => $http . '/api/user/me',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'user' => [
                                'id' => '用户id',
                                'code' => '医脉码',
                                'phone' => '用户注册手机号',
                                'email' => '用户邮箱',
                                'rong_yun_token' => '融云token',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'sex' => '性别',
                                'province' => [
                                    'id' => '用户所在省份ID',
                                    'name' => '用户所在省份名称'
                                ],
                                'city' => [
                                    'id' => '用户所在城市ID',
                                    'name' => '用户所在城市名称'
                                ],
                                'hospital' => [
                                    'id' => '用户所在医院ID',
                                    'name' => '用户所在医院名称'
                                ],
                                'department' => [
                                    'id' => '用户所在科室ID',
                                    'name' => '用户所在科室名称'
                                ],
                                'job_title' => '用户职称',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'auth_img' => '认证图片url,相对路径; url用逗号相隔,最多5张;',
                                'fee_switch' => '1:开, 0:关',
                                'fee' => '接诊收费金额',
                                'fee_face_to_face' => '当面咨询收费金额',
                                'admission_set_fixed' => [
                                    '说明' => '接诊时间设置,固定排班; 接收json,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                    '格式案例' => [
                                        'week' => 'sun',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'admission_set_flexible' => [
                                    '说明' => '接诊时间设置,灵活排班; 接收json,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                    '格式案例' => [
                                        'date' => '2016-06-23',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'verify_switch' => '隐私设置: 添加好友验证开关; 默认值为1,即开',
                                'friends_friends_appointment_switch' => '隐私设置: 好友的好友可以向我发起约诊开关; 默认值为0,即关',
                                'inviter' => '邀请者'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '通过用户ID查询其他医生的信息' => [
                        'url' => $http . '/api/user/{doctor_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '请前台判断是否在查询自己,自己的信息在登陆时已经有全部的了,而且看自己的没有中间的两个按钮',
                        'response' => [
                            'user' => [
                                'is_friend' => '决定按钮的布局; true | false',
                                'id' => '用户id',
                                'code' => '医脉码',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称',
                                'province' => '用户所在省份名称',
                                'city' => '用户所在城市名称',
                                'hospital' => '用户所在医院名称',
                                'department' => '用户所在科室名称',
                                'college' => '用户所在院校名称',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'common_friend_list' => [
                                    'id' => '用户id',
                                    'head_url' => '头像URL',
                                    'is_auth' => '是否认证,1为认证,0为未认证'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '通过用户手机号查询其他医生的信息' => [
                        'url' => $http . '/api/user/phone/{phone}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'user' => [
                                'is_friend' => 'true | false',
                                'id' => '用户id',
                                'code' => '医脉码',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称',
                                'province' => '用户所在省份名称',
                                'city' => '用户所在城市名称',
                                'hospital' => '用户所在医院名称',
                                'department' => '用户所在科室名称',
                                'college' => '用户所在院校名称',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'common_friend_list' => [
                                    'id' => '用户id',
                                    'head_url' => '头像URL',
                                    'is_auth' => '是否认证,1为认证,0为未认证'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '搜索医生信息' => [
                        'url' => $http . '/api/user/search',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 必填项,当type为指定内容时为可选项,不过此时将会是全局搜索,返回信息量巨大',
                            'city' => '下拉框选择的城市ID; 可选项; 参数名也可以是city_id',
                            'hospital' => '下拉框选择的医院ID; 可选项; 参数名也可以是hospital_id',
                            'department' => '下拉框选择的科室ID; 可选项; 参数名也可以是dept_id',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式',
                            'type' => '普通搜索,可以不填该项或内容置空; 同医院:same_hospital; 同领域:same_department; 同院校:same_college; 可选项; 也可以使用下面3个专用接口'
                        ],
                        '说明1' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        '说明2' => '当type符合同医院:same_hospital; 同领域:same_department; 同院校:same_college时,返回的users部分没有分组',
                        'response' =>
                            [
                                'provinces' => [
                                    'id' => '省份ID, province_id',
                                    'name' => '省份/直辖市名称'
                                ],
                                'citys' => [
                                    '{province_id}' => [
                                        'id' => '城市ID, city_id',
                                        'name' => '城市名称'
                                    ]
                                ],
                                'hospitals' => [
                                    '默认格式说明' => '例如: hospitals[1][1]可以取到1省1市下的医院列表',
                                    '{province_id}' => [
                                        '{city_id}' => [
                                            '{自增的数据下标,非key}' => [
                                                'id' => '医院ID',
                                                'name' => '城市名称',
                                                'province_id' => '该医院的省id',
                                                'city_id' => '该医院的市id'
                                            ]
                                        ]
                                    ],

                                    '安卓格式说明' => '提交format字段,且值为android时,hospitals会返回该格式 :',
                                    '{自增的数组序号}' => [
                                        'province_id' => '省份ID',
                                        'data' => [
                                            '{自增的数据下标,非key}' => [
                                                'city_id' => '城市ID',
                                                'data' => [
                                                    '{自增的数据下标,非key}' => [
                                                        'id' => '医院ID',
                                                        'name' => '城市名称',
                                                        'province_id' => '该医院的省id',
                                                        'city_id' => '该医院的市id'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                                'departments' => [
                                    'id' => '科室ID',
                                    'name' => '科室名称'
                                ],
                                'count' => '满足条件的医生数量',
                                'users' => [
                                    'id' => '用户ID',
                                    'name' => '用户姓名',
                                    'head_url' => '头像URL',
                                    'job_title' => '职称',
                                    'city' => '所属城市',
                                    'hospital' => [
                                        'id' => '用户所在医院ID',
                                        'name' => '用户所在医院名称'
                                    ],
                                    'department' => [
                                        'id' => '用户所在科室ID',
                                        'name' => '用户所在科室名称'
                                    ],
                                    'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
                                ],
                                'message' => '',
                                'error' => ''
                            ]
                    ],
                    '搜索医生信息，预约医生' => [
                        'url' => $http . '/api/user/search/admissions',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 可选项,为空时将会是全局搜索,返回信息量巨大',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式'
                        ],
                        '说明' => '默认是同城搜索; 会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'response' =>
                            [
                                'provinces' => [
                                    'id' => '省份ID, province_id',
                                    'name' => '省份/直辖市名称'
                                ],
                                'citys' => [
                                    '{province_id}' => [
                                        'id' => '城市ID, city_id',
                                        'name' => '城市名称'
                                    ]
                                ],
                                'hospitals' => [
                                    '默认格式说明' => '例如: hospitals[1][1]可以取到1省1市下的医院列表',
                                    '{province_id}' => [
                                        '{city_id}' => [
                                            '{自增的数据下标,非key}' => [
                                                'id' => '医院ID',
                                                'name' => '城市名称',
                                                'province_id' => '该医院的省id',
                                                'city_id' => '该医院的市id'
                                            ]
                                        ]
                                    ],

                                    '安卓格式说明' => '提交format字段,且值为android时,hospitals会返回该格式 :',
                                    '{自增的数组序号}' => [
                                        'province_id' => '省份ID',
                                        'data' => [
                                            '{自增的数据下标,非key}' => [
                                                'city_id' => '城市ID',
                                                'data' => [
                                                    '{自增的数据下标,非key}' => [
                                                        'id' => '医院ID',
                                                        'name' => '城市名称',
                                                        'province_id' => '该医院的省id',
                                                        'city_id' => '该医院的市id'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                                'departments' => [
                                    'id' => '科室ID',
                                    'name' => '科室名称'
                                ],
                                'count' => '满足条件的医生数量',
                                'users' => [
                                    'friends' => [
                                        'id' => '用户ID',
                                        'name' => '用户姓名',
                                        'head_url' => '头像URL',
                                        'job_title' => '职称',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
                                    ],
                                    'friends-friends' => [
                                        '用户结构' => '同上'
                                    ],
                                    'others' => [
                                        '用户结构' => '在该搜索项中,该数据永远为空数组'
                                    ]
                                ],
                                'message' => '',
                                'error' => ''
                            ]
                    ],
                    '搜索医生信息，同医院' => [
                        'url' => $http . '/api/user/search/same-hospital',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 可选项,为空时将会是全局搜索,返回信息量巨大',
                            'city' => '下拉框选择的城市ID; 可选项; 参数名也可以是city_id',
                            'department' => '下拉框选择的科室ID; 可选项; 参数名也可以是dept_id',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式'
                        ],
                        '说明' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'response' =>
                            [
                                'provinces' => [
                                    'id' => '省份ID, province_id',
                                    'name' => '省份/直辖市名称'
                                ],
                                'citys' => [
                                    '{province_id}' => [
                                        'id' => '城市ID, city_id',
                                        'name' => '城市名称'
                                    ]
                                ],
                                'hospitals' => [
                                    '默认格式说明' => '例如: hospitals[1][1]可以取到1省1市下的医院列表',
                                    '{province_id}' => [
                                        '{city_id}' => [
                                            '{自增的数据下标,非key}' => [
                                                'id' => '医院ID',
                                                'name' => '城市名称',
                                                'province_id' => '该医院的省id',
                                                'city_id' => '该医院的市id'
                                            ]
                                        ]
                                    ],

                                    '安卓格式说明' => '提交format字段,且值为android时,hospitals会返回该格式 :',
                                    '{自增的数组序号}' => [
                                        'province_id' => '省份ID',
                                        'data' => [
                                            '{自增的数据下标,非key}' => [
                                                'city_id' => '城市ID',
                                                'data' => [
                                                    '{自增的数据下标,非key}' => [
                                                        'id' => '医院ID',
                                                        'name' => '城市名称',
                                                        'province_id' => '该医院的省id',
                                                        'city_id' => '该医院的市id'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                                'departments' => [
                                    'id' => '科室ID',
                                    'name' => '科室名称'
                                ],
                                'count' => '满足条件的医生数量',
                                'users' => [
                                    'friends' => [
                                        'id' => '用户ID',
                                        'name' => '用户姓名',
                                        'head_url' => '头像URL',
                                        'job_title' => '职称',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
                                    ],
                                    'friends-friends' => [
                                        '用户结构' => '同上'
                                    ],
                                    'others' => [
                                        '用户结构' => '同上'
                                    ]
                                ],
                                'message' => '',
                                'error' => ''
                            ]
                    ],
                    '搜索医生信息，同领域' => [
                        'url' => $http . '/api/user/search/same-department',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 可选项,为空时将会是全局搜索,返回信息量巨大',
                            'city' => '下拉框选择的城市ID; 可选项; 参数名也可以是city_id',
                            'hospital' => '下拉框选择的医院ID; 可选项; 参数名也可以是hospital_id',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式'
                        ],
                        '说明' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'response' =>
                            [
                                'provinces' => [
                                    'id' => '省份ID, province_id',
                                    'name' => '省份/直辖市名称'
                                ],
                                'citys' => [
                                    '{province_id}' => [
                                        'id' => '城市ID, city_id',
                                        'name' => '城市名称'
                                    ]
                                ],
                                'hospitals' => [
                                    '默认格式说明' => '例如: hospitals[1][1]可以取到1省1市下的医院列表',
                                    '{province_id}' => [
                                        '{city_id}' => [
                                            '{自增的数据下标,非key}' => [
                                                'id' => '医院ID',
                                                'name' => '城市名称',
                                                'province_id' => '该医院的省id',
                                                'city_id' => '该医院的市id'
                                            ]
                                        ]
                                    ],

                                    '安卓格式说明' => '提交format字段,且值为android时,hospitals会返回该格式 :',
                                    '{自增的数组序号}' => [
                                        'province_id' => '省份ID',
                                        'data' => [
                                            '{自增的数据下标,非key}' => [
                                                'city_id' => '城市ID',
                                                'data' => [
                                                    '{自增的数据下标,非key}' => [
                                                        'id' => '医院ID',
                                                        'name' => '城市名称',
                                                        'province_id' => '该医院的省id',
                                                        'city_id' => '该医院的市id'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                                'departments' => [
                                    'id' => '科室ID',
                                    'name' => '科室名称'
                                ],
                                'count' => '满足条件的医生数量',
                                'users' => [
                                    'friends' => [
                                        'id' => '用户ID',
                                        'name' => '用户姓名',
                                        'head_url' => '头像URL',
                                        'job_title' => '职称',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
                                    ],
                                    'friends-friends' => [
                                        '用户结构' => '同上'
                                    ],
                                    'others' => [
                                        '用户结构' => '同上'
                                    ]
                                ],
                                'message' => '',
                                'error' => ''
                            ]
                    ],
                    '搜索医生信息，同院校' => [
                        'url' => $http . '/api/user/search/same-college',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 可选项,为空时将会是全局搜索,返回信息量巨大',
                            'city' => '下拉框选择的城市ID; 可选项; 参数名也可以是city_id',
                            'hospital' => '下拉框选择的医院ID; 可选项; 参数名也可以是hospital_id',
                            'department' => '下拉框选择的科室ID; 可选项; 参数名也可以是dept_id',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式'
                        ],
                        '说明' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'response' =>
                            [
                                'provinces' => [
                                    'id' => '省份ID, province_id',
                                    'name' => '省份/直辖市名称'
                                ],
                                'citys' => [
                                    '{province_id}' => [
                                        'id' => '城市ID, city_id',
                                        'name' => '城市名称'
                                    ]
                                ],
                                'hospitals' => [
                                    '默认格式说明' => '例如: hospitals[1][1]可以取到1省1市下的医院列表',
                                    '{province_id}' => [
                                        '{city_id}' => [
                                            '{自增的数据下标,非key}' => [
                                                'id' => '医院ID',
                                                'name' => '城市名称',
                                                'province_id' => '该医院的省id',
                                                'city_id' => '该医院的市id'
                                            ]
                                        ]
                                    ],

                                    '安卓格式说明' => '提交format字段,且值为android时,hospitals会返回该格式 :',
                                    '{自增的数组序号}' => [
                                        'province_id' => '省份ID',
                                        'data' => [
                                            '{自增的数据下标,非key}' => [
                                                'city_id' => '城市ID',
                                                'data' => [
                                                    '{自增的数据下标,非key}' => [
                                                        'id' => '医院ID',
                                                        'name' => '城市名称',
                                                        'province_id' => '该医院的省id',
                                                        'city_id' => '该医院的市id'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                                'departments' => [
                                    'id' => '科室ID',
                                    'name' => '科室名称'
                                ],
                                'count' => '满足条件的医生数量',
                                'users' => [
                                    'friends' => [
                                        'id' => '用户ID',
                                        'name' => '用户姓名',
                                        'head_url' => '头像URL',
                                        'job_title' => '职称',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
                                    ],
                                    'friends-friends' => [
                                        '用户结构' => '同上'
                                    ],
                                    'others' => [
                                        '用户结构' => '同上'
                                    ]
                                ],
                                'message' => '',
                                'error' => ''
                            ]
                    ],
                    '修改个人信息/修改密码/修改接诊收费信息/修改隐私设置' => [
                        'url' => $http . '/api/user',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '以下form-data项均为可选项,修改任意一个或几个都可以,有什么数据加什么字段',
                        'form-data' => [
                            'password' => '用户密码',
                            'name' => '用户姓名',
                            'head_img' => '用户头像; 直接POST文件,支持后缀:jpg/jpeg/png',
                            'sex' => '性别',
                            'province' => '用户所在省份ID',
                            'city' => '用户所属城市ID',
                            'hospital' => '用户所属医院ID; 如果该处提交的不是医院ID，则会自动创建该医院后并返回',
                            'department' => '用户所属部门ID',
                            'job_title' => '用户职称',
                            'college' => '用户所属院校ID',
                            'ID_number' => '身份证号',
                            'tags' => '特长/标签',
                            'personal_introduction' => '个人简介',
                            'fee_switch' => '接诊收费开关, 1:开, 0:关(默认值)',
                            'fee' => '接诊收费金额,默认300',
                            'fee_face_to_face' => '当面咨询收费金额,默认100',
                            'verify_switch' => '隐私设置: 添加好友验证开关; 默认值为1,即开',
                            'friends_friends_appointment_switch' => '隐私设置: 好友的好友可以向我发起约诊开关; 默认值为0,即关'
                        ],
                        'response' => [
                            'user' => [
                                'id' => '用户id',
                                'code' => '医脉码',
                                'phone' => '用户注册手机号',
                                'email' => '用户邮箱',
                                'rong_yun_token' => '融云token',
                                'name' => '用户姓名',
                                'head_url' => '头像URL; 相对地址,需要拼服务器域名或ip,例如:回传/uploads/a.jpg,要拼成:http://yimai.com/uploads/a.jpg; 注意url中没有api',
                                'sex' => '性别',
                                'province' => [
                                    'id' => '用户所在省份ID',
                                    'name' => '用户所在省份名称'
                                ],
                                'city' => [
                                    'id' => '用户所在城市ID',
                                    'name' => '用户所在城市名称'
                                ],
                                'hospital' => [
                                    'id' => '用户所在医院ID',
                                    'name' => '用户所在医院名称'
                                ],
                                'department' => [
                                    'id' => '用户所在科室ID',
                                    'name' => '用户所在科室名称'
                                ],
                                'job_title' => '用户职称',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'auth_img' => '认证图片url,相对路径; url用逗号相隔,最多5张;',
                                'fee_switch' => '1:开, 0:关',
                                'fee' => '接诊收费金额',
                                'fee_face_to_face' => '当面咨询收费金额',
                                'admission_set_fixed' => [
                                    '说明' => '接诊时间设置,固定排班; 接收json,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                    '格式案例' => [
                                        'week' => 'sun',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'admission_set_flexible' => [
                                    '说明' => '接诊时间设置,灵活排班; 接收json,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                    '格式案例' => [
                                        'date' => '2016-06-23',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'verify_switch' => '隐私设置: 添加好友验证开关; 默认值为1,即开',
                                'friends_friends_appointment_switch' => '隐私设置: 好友的好友可以向我发起约诊开关; 默认值为0,即关',
                                'inviter' => '邀请者'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '上传认证图片' => [
                        'url' => $http . '/api/user/upload-auth-img',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'img-1' => '认证图片; 直接POST文件,支持后缀:jpg/jpeg/png',
                            'img-2' => '认证图片; 直接POST文件,支持后缀:jpg/jpeg/png; 可选',
                            'img-3' => '认证图片; 直接POST文件,支持后缀:jpg/jpeg/png; 可选',
                            'img-4' => '认证图片; 直接POST文件,支持后缀:jpg/jpeg/png; 可选',
                            'img-5' => '认证图片; 直接POST文件,支持后缀:jpg/jpeg/png; 可选',
                        ],
                        'response' => [
                            'url' => '压缩后的图片访问url链接,可直接用于阅览; 多个链接由逗号分隔',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                ],

                '省市信息' => [
                    '省市列表' => [
                        'url' => $http . '/api/city',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'provinces' => [
                                'id' => '省份ID, province_id',
                                'name' => '省份/直辖市名称'
                            ],
                            'citys' => [
                                'id' => '城市ID',
                                'province_id' => '省份ID',
                                'name' => '城市名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '省市列表-按省分组' => [
                        'url' => $http . '/api/city/group',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'provinces' => [
                                'id' => '省份ID, province_id',
                                'name' => '省份/直辖市名称'
                            ],
                            'citys' => [
                                '{province_id}' => [
                                    'id' => '城市ID',
                                    'name' => '城市名称'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '医院信息' => [
                    '全部医院' => [
                        'url' => $http . '/api/hospital',
                        'method' => 'GET',
                        'params' => [
                            'token' => '',
                            'page' => '页码,一页100; 没有填页码默认是第一页'
                        ],
                        'response' => [
                            'data' => [
                                'id' => '医院ID',
                                'area' => '所属地区',
                                'province' => '省份',
                                'city' => '城市',
                                'name' => '医院名称',
                                '3a' => '是否为三甲医院; 1:三甲, 0:非三甲',
                                'top' => '顶级科室的数量',
                            ],
                            'meta' => [
                                'pagination' => [
                                    'total' => '医院总共的数量',
                                    'count' => '该次请求获取的数量',
                                    'per_page' => '每页将请求数据量',
                                    'current_page' => '当前页码(page)',
                                    'total_pages' => '总共页码(page)',
                                    'links' => [
                                        'next' => '会自动生成下一页链接,:http://localhost/api/hospital?page=2'
                                    ]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '单个医院' => [
                        'url' => $http . '/api/hospital/{hospital_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '医院ID',
                                'area' => '所属地区',
                                'province' => '省份',
                                'city' => '城市',
                                'name' => '医院名称',
                                '3a' => '是否为三甲医院; 1:三甲, 0:非三甲',
                                'top' => '顶级科室的数量',
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '属于某个城市下的医院' => [
                        'url' => $http . '/api/hospital/city/{city_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '已按三甲医院顺序排序',
                        'response' => [
                            'data' => [
                                'id' => '医院ID',
                                'name' => '医院名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '模糊查询某个医院名称' => [
                        'url' => $http . '/api/hospital/search/{search_field}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '{search_field}字段传中文可能需要转码',
                        'response' => [
                            'data' => [
                                'id' => '医院ID',
                                'name' => '医院名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '医院搜索,约诊确定后专用' => [
                        'url' => $http . '/api/hospital/search/admissions',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 必填项,当type为指定内容时为可选项,不过此时将会是全局搜索,返回信息量巨大',
                            'province_id' => '下拉框选择的省份ID; 可选项',
                            'city_id' => '下拉框选择的城市ID; 可选项',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式',
                        ],
                        '说明' => '如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'response' =>
                            [
                                'provinces' => [
                                    'id' => '省份ID, province_id',
                                    'name' => '省份/直辖市名称'
                                ],
                                'citys' => [
                                    '{province_id}' => [
                                        'id' => '城市ID, city_id',
                                        'name' => '城市名称'
                                    ]
                                ],
                                'hospitals' => [
                                    '默认格式说明' => '例如: hospitals[1][1]可以取到1省1市下的医院列表',
                                    '{province_id}' => [
                                        '{city_id}' => [
                                            '{自增的数据下标,非key}' => [
                                                'id' => '医院ID',
                                                'name' => '城市名称',
                                                'address' => '医院地址',
                                                'province_id' => '该医院的省id',
                                                'city_id' => '该医院的市id'
                                            ]
                                        ]
                                    ],

                                    '安卓格式说明' => '提交format字段,且值为android时,hospitals会返回该格式 :',
                                    '{自增的数组序号}' => [
                                        'province_id' => '省份ID',
                                        'data' => [
                                            '{自增的数据下标,非key}' => [
                                                'city_id' => '城市ID',
                                                'data' => [
                                                    '{自增的数据下标,非key}' => [
                                                        'id' => '医院ID',
                                                        'name' => '城市名称',
                                                        'address' => '医院地址',
                                                        'province_id' => '该医院的省id',
                                                        'city_id' => '该医院的市id'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                                'message' => '',
                                'error' => ''
                            ]
                    ],
                ],

                '院校信息' => [
                    '所有院校' => [
                        'url' => $http . '/api/college/all',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '院校ID',
                                'name' => '院校名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '科室信息' => [
                    '所有科室' => [
                        'url' => $http . '/api/dept',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '科室ID',
                                'name' => '科室名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '特长标签信息' => [
                    '所有标签' => [
                        'url' => $http . '/api/tag/all',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '特长标签ID',
                                'name' => '特长标签名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '医脉资源' => [
                    '新增朋友/申请好友' => [
                        'url' => $http . '/api/relation/add-friend',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '用户ID； 三选一即可',
                            'phone' => '用户手机号； 三选一即可',
                            'code' => '用户医脉码； 三选一即可'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '同意/确定申请' => [
                        'url' => $http . '/api/user/relation/confirm',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '用户ID'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '一度医脉(四部分数据,多用于首次/当天首次打开)' => [
                        'url' => $http . '/api/relation',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'same' => [
                                'hospital' => '同医院的人数',
                                'department' => '同领域的人数',
                                'college' => '同学校的人数'
                            ],
                            'unread' => '好友信息的未读数量',
                            'count' => [
                                'doctor' => '我的朋友中共有多少名医生',
                                'hospital' => '我的朋友中分别属于多少家医院'
                            ],
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'job_title' => '职称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '一度医脉(两部分数据,多用于打开后第一次之后的刷新数据用)' => [
                        'url' => $http . '/api/relation/friends',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'count' => [
                                'doctor' => '我的朋友中共有多少名医生',
                                'hospital' => '我的朋友中分别属于多少家医院'
                            ],
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'job_title' => '职称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '二度医脉(两部分数据)' => [
                        'url' => $http . '/api/relation/friends-friends',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'friends中的数据块已按common_friend_count的倒序排序',
                        'response' => [
                            'count' => [
                                'doctor' => '我的朋友中共有多少名医生',
                                'hospital' => '我的朋友中分别属于多少家医院'
                            ],
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'job_title' => '职称',
                                'common_friend_count' => '共同好友数量'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '共同好友' => [
                        'url' => $http . '/api/relation/common-friends/{friend-id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            '{自增的数组序号}' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => [
                                    'id' => '用户所在医院ID',
                                    'name' => '用户所在医院名称'
                                ],
                                'department' => [
                                    'id' => '用户所在科室ID',
                                    'name' => '用户所在科室名称'
                                ],
                                'job_title' => '职称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '新朋友' => [
                        'url' => $http . '/api/relation/new-friends',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'friends中的数据块已按添加好友的时间倒序排序; 获取之后,该次所有数据的未读状态将自动置为已读',
                        'response' => [
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'unread' => '未读状态,1为已读,0为未读',
                                'status' => '与好友的状态; isFriend | waitForSure | waitForFriendAgree',
                                'word' => '显示文案'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],

                    '同步最近联系人记录到服务器' => [
                        'url' => $http . '/api/relation/push-recent-contacts',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id_list' => '最近联系人ID list; 例如: 1,2,3,4,5 ; 最长12个人'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],

                    '给好友添加备注' => [
                        'url' => $http . '/api/relation/remarks',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'friend_id' => '好友的用户ID',
                            'remarks' => '备注内容'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],

                    '删除好友关系' => [
                        'url' => $http . '/api/relation/del',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'friend_id' => '好友的用户ID'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],

                    '上传通讯录信息' => [
                        'url' => $http . '/api/relation/upload-address-book',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'content' => 'json格式的全部通讯录信息; 格式:[{"name":"","phone":""},{"name":"","phone":""}]'
                        ],
                        '测试数据' => [
                            'data' => '[{"phone":"18712345678","name":"187"},{"phone":"18611175661","name":"187"},{"phone":"18611111111","name":"没有加入"}]',
                            '说明' => '用186用户登录的话,上面的数据刚好是一个在通讯里且加好友了,一个在通讯里但没在医脉加好友,一个在通讯录里且没加入医脉'
                        ],
                        '说明' => 'friends是在通讯里加入了医脉,但没在医脉中互加好友的部分; others是未加入医脉的通讯里好友名单',
                        'response' => [
                            'data' => [
                                'friends' => [
                                    'id' => '用户ID',
                                    'name' => '用户姓名',
                                    'head_url' => '头像URL',
                                    'hospital' => [
                                        'id' => '用户所在医院ID',
                                        'name' => '用户所在医院名称'
                                    ],
                                    'department' => [
                                        'id' => '用户所在科室ID',
                                        'name' => '用户所在科室名称'
                                    ],
                                    'job_title' => '职称'
                                ],
                                'others' => [
                                    'name' => '姓名',
                                    'phone' => '头像URL'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '广播信息' => [
                    '所有广播' => [
                        'url' => $http . '/api/radio',
                        'method' => 'GET',
                        'params' => [
                            'token' => '',
                            'page' => '页码,一页4个; 没有填页码默认是第一页'
                        ],
                        'response' => [
                            'data' => [
                                'id' => '广播ID',
                                'name' => '广播标题',
                                'url' => '广播链接; 相对地址; 例如:/article/1, 前台请拼成: http://101.201.40.220/article/1 , 即可直接访问,不需要其他get参数',
                                'img_url' => '首页图片URL; 相对地址; 例如:/uploads/article/1.png, 前台请拼成: http://101.201.40.220/uploads/article/1.png ,即可直接访问',
                                'author' => '发表人',
                                'time' => '发表时间',
                                'unread' => '是否未读,1为未读,null为已读'
                            ],
                            'meta' => [
                                'pagination' => [
                                    'total' => '广播总共的数量',
                                    'count' => '该次请求获取的数量',
                                    'per_page' => '每页将请求数据量',
                                    'current_page' => '当前页码(page)',
                                    'total_pages' => '总共页码(page)',
                                    'links' => [
                                        'next' => '会自动生成下一页链接,类似于:http://localhost/api/radio?page=2'
                                    ]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '广播已读' => [
                        'url' => $http . '/api/radio/read',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '广播ID'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '约诊' => [
                    '新建约诊' => [
                        'url' => $http . '/api/appointment/new',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'name' => '患者姓名',
                            'phone' => '患者手机号',
                            'sex' => '患者性别,1男0女',
                            'age' => '患者年龄',
                            'history' => '患者现病史',
                            'doctor' => '预约的医生的ID',
                            'date' => '预约日期,最多选择3个,用逗号分隔开即可,例:2016-05-01,2016-05-02; 如果是医生决定就是传0即可。',
                            'am_or_pm' => '预约上/下午,和上面的对应的用逗号分隔开即可,例:am,pm; 如果是医生决定随便传什么,都不会处理,取值时为空',
                        ],
                        'response' => [
                            'id' => '预约码',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '上传图片' => [
                        'url' => $http . '/api/appointment/upload-img',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊预约码',
                            'img' => '病历照片,一张张传; 直接POST文件,支持后缀:jpg/jpeg/png'
                        ],
                        'response' => [
                            'url' => '压缩后的图片访问url链接,可直接用于阅览',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '获取约诊记录(待回复/已回复)' => [
                        'url' => $http . '/api/appointment/list',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '用phone:18712345678,password:123456登陆可以获得所有测试数据',
                        'response' => [
                            'data' => [
                                'wait' => [
                                    [
                                        'id' => '约诊ID',
                                        'doctor_id' => '医生ID',
                                        'doctor_name' => '医生姓名',
                                        'doctor_head_url' => '医生头像',
                                        'doctor_job_title' => '医生头衔',
                                        'doctor_is_auth' => '医生是否认证',
                                        'patient_name' => '患者姓名',
                                        'time' => '时间',
                                        'status' => '状态'
                                    ]
                                ],
                                'already' => [
                                    [
                                        'id' => '约诊ID',
                                        'doctor_id' => '医生ID',
                                        'doctor_name' => '医生姓名',
                                        'doctor_head_url' => '医生头像',
                                        'doctor_job_title' => '医生头衔',
                                        'doctor_is_auth' => '医生是否认证',
                                        'patient_name' => '患者姓名',
                                        'time' => '时间',
                                        'status' => '状态'
                                    ]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '获取我的代约详细信息' => [
                        'url' => $http . '/api/appointment/detail/{appointment_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '特别说明1' => '设计样式图《约诊回复3-3、3-4、3-5》内容条目有些不对,参照样式即可,以API返回数据为准',
                        '特别说明2' => '设计样式图中患者电话部分,全部参照《约诊回复3-5》的患者电话样式',
                        '测试数据1' => '5个等待状态1的可测试约诊号:011605130001,011605130002,011605130003,011605130004,011605130005',
                        '测试数据2' => '5个等待状态2的可测试约诊号:011605260001,011605260002,011605260003,011605260004,011605260005',
                        '测试数据3' => '7个取消状态1的可测试约诊号:011605150001,011605150002,011605150003,011605150004,011605150005,011605150006,011605150007',
                        '测试数据4' => '7个取消状态2的可测试约诊号:011605160001,011605160002,011605160003,011605160004,011605160005,011605160006,011605160007',
                        '测试数据5' => '3个关闭状态1的可测试约诊号:011605140001,011605140002,011605140003',
                        '测试数据6' => '3个关闭状态2的可测试约诊号:011605240001,011605240002,011605240003',
                        '测试数据7' => '2个完成状态1的可测试约诊号:011605180001,011605180002',
                        '测试数据8' => '2个完成状态2的可测试约诊号:011605190001,011605190002',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '所属科室',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '顶部进度',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    '内容' => [[
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                '内容' => [[
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午; 多条时间信息用逗号隔开,展示时则是换行展示,例如:2016-05-12 上午,2016-05-13 下午'
                                                ], []]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                        [
                                            'time' => '时间轴左侧的时间, null为没有',
                                            'info' => [
                                                'text' => '文案描述',
                                                'other' => 'null为没有'
                                            ],
                                            'type' => '决定使用什么icon; begin | wait'
                                        ]]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '我的接诊' => [
                    '同意接诊' => [
                        'url' => $http . '/api/admissions/agree',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊ID',
                            'visit_time' => '接诊时间',
                            'supplement' => '附加信息; 可选项',
                            'remark' => '补充说明; 可选项'
                        ],
                        '说明' => '用phone:18612345678,password:123456登陆可以操作所有测试数据; 返回数据格式和【获取我的约诊详细信息】一模一样',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '所属科室',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '顶部进度',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    '内容' => [[
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                '内容' => [[
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午; 多条时间信息用逗号隔开,展示时则是换行展示,例如:2016-05-12 上午,2016-05-13 下午'
                                                ], []]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                        [
                                            'time' => '时间轴左侧的时间, null为没有',
                                            'info' => [
                                                'text' => '文案描述',
                                                'other' => 'null为没有'
                                            ],
                                            'type' => '决定使用什么icon; begin | wait'
                                        ]]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '拒绝接诊' => [
                        'url' => $http . '/api/admissions/refusal',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊ID',
                            'reason' => '拒绝原因'
                        ],
                        '说明' => '用phone:18612345678,password:123456登陆可以操作所有测试数据; 返回数据格式和【获取我的约诊详细信息】一模一样',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '所属科室',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '顶部进度',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    '内容' => [[
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                '内容' => [[
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午; 多条时间信息用逗号隔开,展示时则是换行展示,例如:2016-05-12 上午,2016-05-13 下午'
                                                ], []]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                        [
                                            'time' => '时间轴左侧的时间, null为没有',
                                            'info' => [
                                                'text' => '文案描述',
                                                'other' => 'null为没有'
                                            ],
                                            'type' => '决定使用什么icon; begin | wait'
                                        ]]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '转诊' => [
                        'url' => $http . '/api/admissions/transfer',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊ID',
                            'doctor_id' => '转诊至哪个医生的ID'
                        ],
                        '说明' => 'HTTP状态204; 会触发一个通知给新的医生; 点击转诊跳转到:2.4-约诊 =》预约_0006_预约医生7.png,只有医生可以修改',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '完成接诊' => [
                        'url' => $http . '/api/admissions/complete',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊ID'
                        ],
                        '说明' => '用phone:18612345678,password:123456登陆可以操作所有测试数据; 返回数据格式和【获取我的约诊详细信息】一模一样',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '手机号码',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '顶部进度',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    '内容' => [[
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                '内容' => [[
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午; 多条时间信息用逗号隔开,展示时则是换行展示,例如:2016-05-12 上午,2016-05-13 下午'
                                                ], []]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                        [
                                            'time' => '时间轴左侧的时间, null为没有',
                                            'info' => [
                                                'text' => '文案描述',
                                                'other' => 'null为没有'
                                            ],
                                            'type' => '决定使用什么icon; begin | wait'
                                        ]]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '医生改期' => [
                        'url' => $http . '/api/admissions/rescheduled',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊ID',
                            'visit_time' => '改期接诊时间'
                        ],
                        '说明' => '用phone:18612345678,password:123456登陆可以操作所有测试数据; 返回数据格式和【获取我的约诊详细信息】一模一样',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '所属科室',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '顶部进度',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    '内容' => [[
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                '内容' => [[
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午; 多条时间信息用逗号隔开,展示时则是换行展示,例如:2016-05-12 上午,2016-05-13 下午'
                                                ], []]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                        [
                                            'time' => '时间轴左侧的时间, null为没有',
                                            'info' => [
                                                'text' => '文案描述',
                                                'other' => 'null为没有'
                                            ],
                                            'type' => '决定使用什么icon; begin | wait'
                                        ]]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '取消接诊' => [
                        'url' => $http . '/api/admissions/cancel',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊ID',
                            'reason' => '取消原因'
                        ],
                        '说明' => '用phone:18612345678,password:123456登陆可以操作所有测试数据; 返回数据格式和【获取我的约诊详细信息】一模一样',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '所属科室',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '顶部进度',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    '内容' => [[
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                '内容' => [[
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午; 多条时间信息用逗号隔开,展示时则是换行展示,例如:2016-05-12 上午,2016-05-13 下午'
                                                ], []]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                        [
                                            'time' => '时间轴左侧的时间, null为没有',
                                            'info' => [
                                                'text' => '文案描述',
                                                'other' => 'null为没有'
                                            ],
                                            'type' => '决定使用什么icon; begin | wait'
                                        ]]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '我的接诊列表(待回复/待完成/已结束)' => [
                        'url' => $http . '/api/admissions/list',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '用phone:18612345678,password:123456登陆可以获得所有测试数据',
                        'response' => [
                            'data' => [
                                'wait_reply' => [
                                    [
                                        'id' => '约诊ID',
                                        'doctor_id' => '医生ID',
                                        'doctor_name' => '医生姓名',
                                        'doctor_head_url' => '医生头像',
                                        'doctor_job_title' => '医生头衔',
                                        'doctor_is_auth' => '医生是否认证',
                                        'hospital' => '医院',
                                        'patient_name' => '患者姓名',
                                        'patient_head_url' => '患者头像',
                                        'patient_gender' => '患者性别,1:男,0:女',
                                        'patient_age' => '患者年龄',
                                        'time' => '时间',
                                        'status' => '状态',
                                        'who' => '谁发起的代约',
                                    ]
                                ],
                                'wait_complete' => [
                                    [
                                        '结构' => '同上'
                                    ]
                                ],
                                'completed' => [
                                    [
                                        '结构' => '同上'
                                    ]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '获取我的约诊详细信息' => [
                        'url' => $http . '/api/admissions/detail/{appointment_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '还未做完,可以测试:/api/appointment/detail/011605130001?token= ',
                        '特别说明1' => '设计样式图《约诊回复3-3、3-4、3-5》内容条目有些不对,参照样式即可,以API返回数据为准',
                        '特别说明2' => '设计样式图中患者电话部分,全部参照《约诊回复3-5》的患者电话样式',
                        '测试数据1' => '5个等待状态1的可测试约诊号:011605130001,011605130002,011605130003,011605130004,011605130005',
                        '测试数据2' => '5个等待状态2的可测试约诊号:011605260001,011605260002,011605260003,011605260004,011605260005',
                        '测试数据3' => '7个取消状态1的可测试约诊号:011605150001,011605150002,011605150003,011605150004,011605150005,011605150006,011605150007',
                        '测试数据4' => '7个取消状态2的可测试约诊号:011605160001,011605160002,011605160003,011605160004,011605160005,011605160006,011605160007',
                        '测试数据5' => '3个关闭状态1的可测试约诊号:011605140001,011605140002,011605140003',
                        '测试数据6' => '3个关闭状态2的可测试约诊号:011605240001,011605240002,011605240003',
                        '测试数据7' => '2个完成状态1的可测试约诊号:011605180001,011605180002',
                        '测试数据8' => '2个完成状态2的可测试约诊号:011605190001,011605190002',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '手机号码',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '顶部进度',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    '内容' => [[
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                '内容' => [[
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午; 多条时间信息用逗号隔开,展示时则是换行展示,例如:2016-05-12 上午,2016-05-13 下午'
                                                ], []]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                        [
                                            'time' => '时间轴左侧的时间, null为没有',
                                            'info' => [
                                                'text' => '文案描述',
                                                'other' => 'null为没有'
                                            ],
                                            'type' => '决定使用什么icon; begin | wait'
                                        ]]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '当面咨询' => [
                    '新建当面咨询' => [
                        'url' => $http . '/api/f2f-advice/new',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'phone' => '患者手机号',
                            'name' => '患者姓名'
                        ],
                        'response' => [
                            'data' => [
                                'id' => '当面咨询ID',
                                'price' => '总共支付的价格,含医生收入和平台收入',
                                'qr_code' => '提供扫描支付的二维码url; 相对地址;'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                ],

                '患者信息' => [
                    '所有广播' => [
                        'url' => $http . '/api/patient/get-by-phone',
                        'method' => 'GET',
                        'params' => [
                            'token' => '',
                            'phone' => '患者手机号'
                        ],
                        '说明' => '没有注册,则返回信息为[]',
                        'response' => [
                            'data' => [
                                'id' => '患者ID',
                                'phone' => '患者手机号',
                                'name' => '患者姓名',
                                'sex' => '患者性别',
                                'age' => '患者年龄'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '我的接诊信息(别的医生向我发起的)' => [
                    '全部信息' => [
                        'url' => $http . '/api/msg/admissions/all',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '',
                        'response' => [
                            'data' => [
                                'id' => '消息ID',
                                'appointment_id' => '约诊号; 用来跳转到对应的【我的接诊】记录',
                                'text' => '显示文案',
                                'type' => '是否重要,0为不重要,1为重要; 重要的内容必须点开告知服务器变为已读; 不重要内容点开列表就全部变已读',
                                'read' => '是否已读,0为未读,1为已读; 该状态后期会将type为0的,获取时直接全部置为已读',
                                'time' => '时间'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '未读信息' => [
                        'url' => $http . '/api/msg/admissions/new',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '',
                        'response' => [
                            'data' => [
                                'id' => '消息ID',
                                'appointment_id' => '约诊号; 用来跳转到对应的【我的接诊】记录',
                                'text' => '显示文案',
                                'type' => '是否重要,0为不重要,1为重要; 重要的内容必须点开告知服务器变为已读; 不重要内容点开列表就全部变已读',
                                'read' => '是否已读,0为未读,1为已读; 该状态后期会将type为0的,获取时直接全部置为已读',
                                'time' => '时间'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '发送已读状态更新' => [
                        'url' => $http . '/api/msg/admissions/read',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '消息ID'
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                ],

                '预约记录信息(我向别的医生发起的)' => [
                    '全部信息' => [
                        'url' => $http . '/api/msg/appointment/all',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '',
                        'response' => [
                            'data' => [
                                'id' => '消息ID',
                                'appointment_id' => '约诊号; 用来跳转到对应的【预约记录】记录',
                                'text' => '显示文案',
                                'type' => '是否重要,0为不重要,1为重要; 重要的内容必须点开告知服务器变为已读; 不重要内容点开列表就全部变已读',
                                'read' => '是否已读,0为未读,1为已读; 该状态后期会将type为0的,获取时直接全部置为已读',
                                'time' => '时间'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '未读信息' => [
                        'url' => $http . '/api/msg/appointment/new',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '',
                        'response' => [
                            'data' => [
                                'id' => '消息ID',
                                'appointment_id' => '约诊号; 用来跳转到对应的【预约记录】记录',
                                'text' => '显示文案',
                                'type' => '是否重要,0为不重要,1为重要; 重要的内容必须点开告知服务器变为已读; 不重要内容点开列表就全部变已读',
                                'read' => '是否已读,0为未读,1为已读; 该状态后期会将type为0的,获取时直接全部置为已读',
                                'time' => '时间'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '发送已读状态更新' => [
                        'url' => $http . '/api/msg/appointment/read',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '消息ID'
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                ]
            ]
        ];

        return $api;
    }

    public function item()
    {
        $http = env('MY_API_HTTP_HEAD', 'http://localhost');

        $api = [
            'API文档目录' => [
                'API文档' => [
                    'url' => $http . '/api',
                    'method' => 'GET'
                ],
                '用户' => [
                    '地址' => [],
                    '包含' => '注册/发送验证码/获取邀请人/登录/重置密码'
                ]
            ],
            '需要Token验证' => [
                '用户' => [
                    '地址' => [],
                    '包含' => '注册/发送验证码/获取邀请人/登录/重置密码'
                ],
                '用户信息' => [
                    '查询个人信息' => [],
                    '修改个人信息' => []
                ],
                '省市信息' => [
                    '省市列表' => [],
                    '省市列表-按省分组' => []
                ],
                '医院信息' => [
                    '单个医院' => [],
                    '属于某个城市下的医院' => [],
                    '模糊查询某个医院名称' => []
                ],
                '科室信息' => [
                    '所有科室' => []
                ],
                '医脉资源' => [
                    '一度医脉(四部分数据,多用于首次/当天首次打开)' => [],
                    '一度医脉(两部分数据,多用于打开后第一次之后的刷新数据用)' => [],
                    '二度医脉(两部分数据)' => [],
                    '新朋友' => []
                ],
                '广播信息' => [
                    '所有广播' => [],
                    '广播已读' => []
                ]

            ]
        ];

        return $api;
    }
}
