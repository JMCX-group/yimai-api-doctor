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
                '医生端域名' => 'http://d.medi-link.cn/',
                '患者端域名' => 'http://p.medi-link.cn/',
                'CMS域名' => 'http://cms.medi-link.cn/',
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
                ],

                '友盟推送说明' => [
                    '广播' => [
                        '场景' => 'CMS发送广播，将会分医生或患者端推送',
                        '传参' => [
                            'action' => 'radio',
                            'data-id' => '广播ID',
                        ],
                        '跳转' => '指定的广播页面',
                    ],
                    '约诊' => [
                        '场景' => '代约医生新建约诊，将会给相应患者推送单播，提示缴费',
                        '传参' => [
                            'action' => 'appointment',
                            'data-id' => '约诊ID',
                        ],
                        '跳转' => '指定的约诊页面',
                    ],
                ]
            ],

            '无需Token验证' => [
                'API文档' => [
                    'url' => $http . '/api',
                    'method' => 'GET'
                ],

                'Banner' => [
                    '全部链接' => [
                        'url' => $http . '/api/get-banner-url',
                        'method' => 'GET',
                        'response' => [
                            'data' => [
                                [
                                    'focus_img_url' => '轮播图URL；绝对地址',
                                    'content_url' => '跳转文章URL；绝对地址'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
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
                    '关于我们' => $http . '/about/contact-us',
                    '医脉简介' => $http . '/about/introduction',
                    '律师信息' => $http . '/about/lawyer',
                    '用户协议' => $http . '/agreement/doctor',
                    '分享文案' => $http . '/share/index'
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
                                'device_token' => '友盟设备token； IOS：64位长，安卓：44位长',
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证；未认证： ；认证成功：completed；认证中：processing；认证失败：fail；',
                                'auth_img' => '认证图片url,相对路径; url用逗号相隔,最多5张;',
                                'qr_code_url' => '提供给其他用户扫描新增好友，数据格式：{"data":"用户ID号", "operation": "操作指令：add_friend"}; 如果判断没有该数据，请随便提交一次修改用户数据的接口，就可以生成，例如把性别1修改成性别1，返回的数据就有这个了',
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
                                'application_card' => '申请名片的状态：1为已申请待审核中（需灰化提交按钮），2为已寄出（需显示快递单号），3为已拒绝（可以再次提交）',
                                'address' => '邮寄地址',
                                'addressee' => '收件人',
                                'receive_phone' => '收件电话',
                                'express_no' => '快递单号',
                                'refuse_info' => '拒绝理由',
                                'role' => '权限，功能还未完成，请先接收到前台',
                                'blacklist' => '黑名单； 用户ID list，用逗号分隔',
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
                                    'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师'
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
                                'radio_unread_count' => '未读的广播数量，已读接口名称：广播已读',
                                'admissions_unread_count' => '未读的接诊信息数量，已读接口名称：发送已读状态更新',
                                'appointment_unread_count' => '未读的约诊信息数量，已读接口名称：发送已读状态更新'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '数据信息' => [
                    '查询医院/医生/约诊数量' => [
                        'url' => $http . '/api/data/auth-column',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'hospital_count' => '医院数量',
                                'doctor_count' => '医生数量',
                                'appointment_count' => '约诊数量'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '查询当前登录医生排班信息' => [
                        '说明' => 'data里总共会有14组数据，依次从当天开始，am和pm后面的是跟的字符串true或false',
                        'url' => $http . '/api/data/scheduling',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '医生ID'
                        ],
                        'response' => [
                            'data' => [
                                [
                                    'date' => '日期，数据示例：2016-06-23',
                                    'am' => '上午，数据示例：true',
                                    'pm' => '下午，数据示例：false',
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
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
                                'device_token' => '友盟设备token； IOS：64位长，安卓：44位长',
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证；未认证： ；认证成功：completed；认证中：processing；认证失败：fail；',
                                'auth_img' => '认证图片url,相对路径; url用逗号相隔,最多5张;',
                                'qr_code_url' => '提供给其他用户扫描新增好友，数据格式：{"data":"用户ID号", "operation": "操作指令：add_friend"}; 如果判断没有该数据，请随便提交一次修改用户数据的接口，就可以生成，例如把性别1修改成性别1，返回的数据就有这个了',
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
                                'application_card' => '申请名片的状态：1为已申请待审核中（需灰化提交按钮），2为已寄出（需显示快递单号），3为已拒绝（可以再次提交）',
                                'address' => '邮寄地址',
                                'addressee' => '收件人',
                                'receive_phone' => '收件电话',
                                'express_no' => '快递单号',
                                'refuse_info' => '拒绝理由',
                                'role' => '权限，功能还未完成，请先接收到前台',
                                'blacklist' => '黑名单； 用户ID list，用逗号分隔',
                                'inviter' => '邀请者'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '通过用户ID查询其他医生的信息' => [
                        '说明' => '请前台判断是否在查询自己,自己的信息在登陆时已经有全部的了,而且看自己的没有中间的两个按钮',
                        'url' => $http . '/api/user/{doctor_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'user' => [
                                'is_friend' => '决定按钮的布局; true | false',
                                'id' => '用户id',
                                'code' => '医脉码',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                'province' => '用户所在省份名称',
                                'city' => '用户所在城市名称',
                                'hospital' => '用户所在医院名称',
                                'department' => '用户所在科室名称',
                                'college' => '用户所在院校名称',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'qr_code_url' => '提供给其他用户扫描新增好友，数据格式：{"data":"用户ID号", "operation": "操作指令：add_friend"}; 如果判断没有该数据，请随便提交一次修改用户数据的接口，就可以生成，例如把性别1修改成性别1，返回的数据就有这个了',
                                'is_auth' => '是否认证；未认证： ；认证成功：completed；认证中：processing；认证失败：fail；',
                                'verify_switch' => '隐私设置: 添加好友验证开关; 默认值为1,即开',
                                'friends_friends_appointment_switch' => '隐私设置: 好友的好友可以向我发起约诊开关; 默认值为0,即关',
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                'province' => '用户所在省份名称',
                                'city' => '用户所在城市名称',
                                'hospital' => '用户所在医院名称',
                                'department' => '用户所在科室名称',
                                'college' => '用户所在院校名称',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'qr_code_url' => '提供给其他用户扫描新增好友，数据格式：{"data":"用户ID号", "operation": "操作指令：add_friend"}; 如果判断没有该数据，请随便提交一次修改用户数据的接口，就可以生成，例如把性别1修改成性别1，返回的数据就有这个了',
                                'is_auth' => '是否认证；未认证： ；认证成功：completed；认证中：processing；认证失败：fail；',
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
                        '说明' => [
                            '1' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                            '2' => '当type符合同医院:same_hospital; 同领域:same_department; 同院校:same_college时,返回的users部分没有分组'
                        ],
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
                            'type' => '普通搜索,可以不填该项或内容置空; 同医院:same_hospital; 同领域:same_department; 同院校:same_college; 可选项; 也可以使用下面3个专用接口',
                            'page' => '页码,每页每组100条用户数据; 可选项; 默认为第一页'
                        ],
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
                                    'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                    'city' => '所属城市',
                                    'hospital' => [
                                        'id' => '用户所在医院ID',
                                        'name' => '用户所在医院名称'
                                    ],
                                    'department' => [
                                        'id' => '用户所在科室ID',
                                        'name' => '用户所在科室名称'
                                    ],
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
                                    'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
                                ],
                                'message' => '',
                                'error' => ''
                            ]
                    ],
                    '搜索医生信息，预约医生' => [
                        '说明' => '默认是同城搜索; 会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'url' => $http . '/api/user/search/admissions',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 可选项,为空时将会是全局搜索,返回信息量巨大',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式',
                            'page' => '页码,每页每组100条用户数据; 可选项; 默认为第一页'
                        ],
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
                                        'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'admission_set_fixed' => [
                                            '说明' => '接诊时间设置,固定排班; 接收json encode字符串,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                            '格式案例' => [
                                                'week' => 'sun',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
                                        ],
                                        'admission_set_flexible' => [
                                            '说明' => '接诊时间设置,灵活排班; 接收json encode字符串,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                            '格式案例' => [
                                                'date' => '2016-06-23',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
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
                        '说明' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'url' => $http . '/api/user/search/same-hospital',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 可选项,为空时将会是全局搜索,返回信息量巨大',
                            'city' => '下拉框选择的城市ID; 可选项; 参数名也可以是city_id',
                            'department' => '下拉框选择的科室ID; 可选项; 参数名也可以是dept_id',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式',
                            'page' => '页码,每页每组100条用户数据; 可选项; 默认为第一页'
                        ],
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
                                        'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'admission_set_fixed' => [
                                            '说明' => '接诊时间设置,固定排班; 接收json encode字符串,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                            '格式案例' => [
                                                'week' => 'sun',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
                                        ],
                                        'admission_set_flexible' => [
                                            '说明' => '接诊时间设置,灵活排班; 接收json encode字符串,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                            '格式案例' => [
                                                'date' => '2016-06-23',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
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
                        '说明' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'url' => $http . '/api/user/search/same-department',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 可选项,为空时将会是全局搜索,返回信息量巨大',
                            'city' => '下拉框选择的城市ID; 可选项; 参数名也可以是city_id',
                            'hospital' => '下拉框选择的医院ID; 可选项; 参数名也可以是hospital_id',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式',
                            'page' => '页码,每页每组100条用户数据; 可选项; 默认为第一页'
                        ],
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
                                        'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'admission_set_fixed' => [
                                            '说明' => '接诊时间设置,固定排班; 接收json encode字符串,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                            '格式案例' => [
                                                'week' => 'sun',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
                                        ],
                                        'admission_set_flexible' => [
                                            '说明' => '接诊时间设置,灵活排班; 接收json encode字符串,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                            '格式案例' => [
                                                'date' => '2016-06-23',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
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
                        '说明' => '会一次传递所有排好序的数据,按3个分组,每个显示2个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
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
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式',
                            'page' => '页码,每页每组100条用户数据; 可选项; 默认为第一页'
                        ],
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
                                        'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                        'city' => '所属城市',
                                        'hospital' => [
                                            'id' => '用户所在医院ID',
                                            'name' => '用户所在医院名称'
                                        ],
                                        'department' => [
                                            'id' => '用户所在科室ID',
                                            'name' => '用户所在科室名称'
                                        ],
                                        'admission_set_fixed' => [
                                            '说明' => '接诊时间设置,固定排班; 接收json encode字符串,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                            '格式案例' => [
                                                'week' => 'sun',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
                                        ],
                                        'admission_set_flexible' => [
                                            '说明' => '接诊时间设置,灵活排班; 接收json encode字符串,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                            '格式案例' => [
                                                'date' => '2016-06-23',
                                                'am' => 'true',
                                                'pm' => 'false',
                                            ]
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
                        '说明' => 'form-data项均为可选项,修改任意一个或几个都可以,有什么数据加什么字段',
                        'url' => $http . '/api/user',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'password' => '用户密码',
                            'device_token' => '友盟设备token； IOS：64位长，安卓：44位长',
                            'name' => '用户姓名',
                            'head_img' => '用户头像; 直接POST文件,支持后缀:jpg/jpeg/png',
                            'sex' => '性别',
                            'province' => '用户所在省份ID',
                            'city' => '用户所属城市ID',
                            'hospital' => '用户所属医院ID; 如果该处提交的不是医院ID，则会自动创建该医院后并返回',
                            'department' => '用户所属部门ID',
                            'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                            'college' => '用户所属院校ID',
                            'ID_number' => '身份证号',
                            'tags' => '特长/标签',
                            'personal_introduction' => '个人简介',
                            'fee_switch' => '接诊收费开关, 1:开, 0:关(默认值)',
                            'fee' => '接诊收费金额,默认300',
                            'fee_face_to_face' => '当面咨询收费金额,默认100',
                            'admission_set_fixed' => [
                                '说明' => '接诊时间设置,固定排班; 接收json encode字符串,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                '格式案例' => [
                                    'week' => 'sun',
                                    'am' => 'true',
                                    'pm' => 'false',
                                ]
                            ],
                            'admission_set_flexible' => [
                                '说明' => '接诊时间设置,灵活排班; 接收json encode字符串,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                '格式案例' => [
                                    'date' => '2016-06-23',
                                    'am' => 'true',
                                    'pm' => 'false',
                                ]
                            ],
                            'verify_switch' => '隐私设置: 添加好友验证开关; 默认值为1,即开',
                            'friends_friends_appointment_switch' => '隐私设置: 好友的好友可以向我发起约诊开关; 默认值为0,即关',
                            'application_card' => '申请名片的状态：1为已申请待审核中（需灰化提交按钮），2为已寄出（需显示快递单号），3为已拒绝（可以再次提交）',
                            'address' => '邮寄地址',
                            'addressee' => '收件人',
                            'receive_phone' => '收件电话',
                            'express_no' => '快递单号',
                            'refuse_info' => '拒绝理由',
                            'blacklist' => '黑名单； 用户ID list，用逗号分隔'
                        ],
                        'response' => [
                            'user' => [
                                'id' => '用户id',
                                'code' => '医脉码',
                                'phone' => '用户注册手机号',
                                'email' => '用户邮箱',
                                'rong_yun_token' => '融云token',
                                'device_token' => '友盟设备token； IOS：64位长，安卓：44位长',
                                'name' => '用户姓名',
                                'head_url' => '头像URL; 绝对地址',
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证；未认证： ；认证成功：completed；认证中：processing；认证失败：fail；',
                                'auth_img' => '认证图片url,相对路径; url用逗号相隔,最多5张;',
                                'qr_code_url' => '提供给其他用户扫描新增好友，数据格式：{"data":"用户ID号", "operation": "操作指令：add_friend"}; 如果判断没有该数据，请随便提交一次修改用户数据的接口，就可以生成，例如把性别1修改成性别1，返回的数据就有这个了',
                                'fee_switch' => '1:开, 0:关',
                                'fee' => '接诊收费金额',
                                'fee_face_to_face' => '当面咨询收费金额',
                                'admission_set_fixed' => [
                                    '说明' => '接诊时间设置,固定排班; 接收json encode字符串,直接存库; 需要存7组数据,week分别是:sun,mon,tue,wed,thu,fri,sat',
                                    '格式案例' => [
                                        'week' => 'sun',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'admission_set_flexible' => [
                                    '说明' => '接诊时间设置,灵活排班; 接收json encode字符串,读取时会自动过滤过期时间; 会有多组数据,格式一致',
                                    '格式案例' => [
                                        'date' => '2016-06-23',
                                        'am' => 'true',
                                        'pm' => 'false',
                                    ]
                                ],
                                'verify_switch' => '隐私设置: 添加好友验证开关; 默认值为1,即开',
                                'friends_friends_appointment_switch' => '隐私设置: 好友的好友可以向我发起约诊开关; 默认值为0,即关',
                                'application_card' => '申请名片的状态：1为已申请待审核中（需灰化提交按钮），2为已寄出（需显示快递单号），3为已拒绝（可以再次提交）',
                                'address' => '邮寄地址',
                                'addressee' => '收件人',
                                'receive_phone' => '收件电话',
                                'express_no' => '快递单号',
                                'refuse_info' => '拒绝理由',
                                'role' => '权限，功能还未完成，请先接收到前台',
                                'blacklist' => '黑名单； 用户ID list，用逗号分隔； 增加/删除都更新改字段即可',
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
                    '修改绑定手机号' => [
                        '说明' => '先获取验证码后，把token、新手机号、新验证码一同传来',
                        'url' => $http . '/api/user/reset-phone',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码',
                            'verify_code' => '4位数字验证码'
                        ],
                        'response' => [
                            'token' => '成功后会返回登录之后的token值',
                            'message' => ''
                        ]
                    ]
                ],

                '钱包' => [
                    '我的钱包' => [
                        'url' => $http . '/api/wallet/info',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'total' => '总额',
                                'billable' => '可提现',
                                'pending' => '待结算',
                                'refunded' => '已提现'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '收支明细列表' => [
                        'url' => $http . '/api/wallet/record',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => 'ID',
                                'name' => '名目名称',
                                'transaction_id' => '交易单号/预约号',
                                'price' => '价格',
                                'type' => '类型：收入/支出',
                                'status' => '状态：还没想好怎么用，先传前台去',
                                'time' => '交易发生时间'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '收支明细列表 - 带分类' => [
                        'url' => $http . '/api/wallet/record',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'type' => '可选项，不填则获取全部； all：全部; billable：可提现; pending：待结算'
                        ],
                        'response' => [
                            'data' => [
                                'id' => 'ID',
                                'name' => '名目名称',
                                'transaction_id' => '交易单号/预约号',
                                'price' => '价格',
                                'type' => '类型：收入/支出',
                                'status' => '状态：还没想好怎么用，先传前台去',
                                'time' => '交易发生时间'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '收支明细细节' => [
                        'url' => $http . '/api/wallet/detail/{record_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => 'ID',
                                'name' => '名目名称',
                                'transaction_id' => '交易单号/预约号',
                                'price' => '价格',
                                'type' => '类型：收入/支出',
                                'status' => '状态：还没想好怎么用，先传前台去',
                                'time' => '交易发生时间'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '申请提现' => [
                        '说明' => '成功后返回HTTP状态204',
                        'url' => $http . '/api/wallet/withdraw',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '银行卡列表的ID，注意不是卡号'
                        ],
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ]
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
                        '说明' => '已按三甲医院顺序排序',
                        'url' => $http . '/api/hospital/city/{city_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
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
                        '说明' => '如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
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
                    ],
                    '分组标签' => [
                        '说明' => '能获取请求者对应的科室下的标签分组，一级科室会有多个二级科室组数据，二级科室只有一组数据',
                        'url' => $http . '/api/tag/group',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                [
                                    'dept' => '二级科室名称',
                                    'tags' => [
                                        'id' => '标签ID',
                                        'name' => '标签名称'
                                    ]
                                ]
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
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '新增全部好友' => [
                        'url' => $http . '/api/relation/add-all',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '用户ID List; 例:1,3,6,7'
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '同意/确定申请' => [
                        'url' => $http . '/api/relation/confirm',
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师'
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '二度医脉(两部分数据)' => [
                        '说明' => 'friends中的数据块已按common_friend_count的倒序排序',
                        'url' => $http . '/api/relation/friends-friends',
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '新朋友' => [
                        '说明' => 'friends中的数据块已按添加好友的时间倒序排序; 获取之后,该次所有数据的未读状态将自动置为已读',
                        'url' => $http . '/api/relation/new-friends',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
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
                        '说明' => [
                            'data' => '[{"phone":"18712345678","name":"187"},{"phone":"18611175661","name":"187"},{"phone":"18611111111","name":"没有加入"}]',
                            '1' => '用186用户登录的话,上面的数据刚好是一个在通讯里且加好友了,一个在通讯里但没在医脉加好友,一个在通讯录里且没加入医脉',
                            '2' => 'friends是在通讯里加入了医脉,但没在医脉中互加好友的部分; others是未加入医脉的通讯里好友名单,需要调用短信接口',
                            '3' => '现在只有2个组:【您在医脉有xx位好友】,【您还有xx位好友未加入医脉】'
                        ],
                        'url' => $http . '/api/relation/upload-address-book',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'content' => 'json格式的全部通讯录信息; 格式:[{"name":"","phone":""},{"name":"","phone":""}]'
                        ],
                        'response' => [
                            'data' => [
                                'friend_count' => '好友数量',
                                'other_count' => '其他数量',
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
                                    'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                    'is_add_friend' => '是否加了好友; 1为加了,不显示添加按钮; 0为未加好友,需要显示添加按钮,调用加好友接口'
                                ],
                                'others' => [
                                    'name' => '姓名',
                                    'phone' => '手机号码',
                                    'sms_status' => '已发送：true；未发送：false; 已发送的不显示发送按钮'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '短信邀请好友' => [
                        'url' => $http . '/api/relation/send-invite',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'phone' => '需要发送短信的手机号码; 例如:18612345678; 如果是添加全部,用逗号隔开,例如:18612345678,18712345678'
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
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
                                'url' => '广播链接; 绝对地址',
                                'img_url' => '首页图片URL; 绝对地址',
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
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '全部已读' => [
                        'url' => $http . '/api/radio/all-read',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '约诊【预约记录】' => [
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
                    '约诊医生 - 更新约诊信息' => [
                        '说明' => '代约医生选择医生后，更新约诊状态，需要传约诊ID和医生ID',
                        'url' => $http . '/api/appointment/update',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '约诊ID',
                            'doctor' => '预约的医生的ID'
                        ],
                        'response' => [
                            'success' => '',
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
                    '获取【预约记录】(待回复/已回复)' => [
                        'url' => $http . '/api/appointment/list',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '状态code说明' => 'wait-0: 待医生确认
                                             wait-1: 待患者付款
                                             wait-2: 患者已付款，待医生确认
                                             wait-3: 医生确认接诊，待面诊
                                             wait-4: 医生改期，待患者确认
                                             wait-5: 患者确认改期，待面诊
                                             close:
                                             close-1: 待患者付款
                                             close-2: 医生过期未接诊,约诊关闭
                                             close-3: 医生拒绝接诊
                                             cancel:
                                             cancel-1: 患者取消约诊; 未付款
                                             cancel-2: 医生取消约诊
                                             cancel-3: 患者取消约诊; 已付款后
                                             cancel-4: 医生改期之后,医生取消约诊;
                                             cancel-5: 医生改期之后,患者取消约诊;
                                             cancel-6: 医生改期之后,患者确认之后,患者取消约诊;
                                             cancel-7: 医生改期之后,患者确认之后,医生取消约诊;
                                             completed:
                                             completed-1:最简正常流程
                                             completed-2:改期后完成',
                        'response' => [
                            'data' => [
                                'wait' => [
                                    [
                                        'id' => '约诊ID',
                                        'doctor_id' => '医生ID',
                                        'doctor_name' => '医生姓名',
                                        'doctor_head_url' => '医生头像',
                                        'doctor_job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                        'doctor_is_auth' => '医生是否认证',
                                        'patient_name' => '患者姓名',
                                        'time' => '时间',
                                        'status' => '状态',
                                        'status_code' => '状态code',
                                    ]
                                ],
                                'already' => [
                                    [
                                        'id' => '约诊ID',
                                        'doctor_id' => '医生ID',
                                        'doctor_name' => '医生姓名',
                                        'doctor_head_url' => '医生头像',
                                        'doctor_job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                        'doctor_is_auth' => '医生是否认证',
                                        'patient_name' => '患者姓名',
                                        'time' => '时间',
                                        'status' => '状态',
                                        'status_code' => '状态code',
                                    ]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '获取【预约记录】详细信息' => [
                        '说明' => '',
                        'url' => $http . '/api/appointment/detail/{appointment_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'patient_demand' => [
                                'doctor_name' => '患者所需的医生姓名',
                                'hospital' => '患者所需的医院',
                                'department' => '患者所需的科室',
                                'job_title' => '患者所需的医生头衔'
                            ],
                            'basic_info' => [
                                'appointment_id' => '约诊ID',
                                'history' => '现病史',
                                'img_url' => '辅助检查',
                                'date' => '就诊时间',
                                'hospital' => '就诊医院',
                                'remark' => '补充说明',
                                'supplement' => '就诊须知'
                            ],
                            'doctor_info' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                                ],
                                'status_code' => '状态CODE'
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
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                    '【我的接诊】列表(待回复/待完成/已结束)' => [
                        'url' => $http . '/api/admissions/list',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '状态code说明' => 'wait-0: 待医生确认
                                             wait-1: 待患者付款
                                             wait-2: 患者已付款，待医生确认
                                             wait-3: 医生确认接诊，待面诊
                                             wait-4: 医生改期，待患者确认
                                             wait-5: 患者确认改期，待面诊
                                             close:
                                             close-1: 待患者付款
                                             close-2: 医生过期未接诊,约诊关闭
                                             close-3: 医生拒绝接诊
                                             cancel:
                                             cancel-1: 患者取消约诊; 未付款
                                             cancel-2: 医生取消约诊
                                             cancel-3: 患者取消约诊; 已付款后
                                             cancel-4: 医生改期之后,医生取消约诊;
                                             cancel-5: 医生改期之后,患者取消约诊;
                                             cancel-6: 医生改期之后,患者确认之后,患者取消约诊;
                                             cancel-7: 医生改期之后,患者确认之后,医生取消约诊;
                                             completed:
                                             completed-1:最简正常流程
                                             completed-2:改期后完成',
                        'response' => [
                            'data' => [
                                'wait_reply' => [
                                    [
                                        'id' => '约诊ID',
                                        'doctor_id' => '医生ID',
                                        'doctor_name' => '医生姓名',
                                        'doctor_head_url' => '医生头像',
                                        'doctor_job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                        'doctor_is_auth' => '医生是否认证',
                                        'hospital' => '医院',
                                        'patient_name' => '患者姓名',
                                        'patient_head_url' => '患者头像',
                                        'patient_gender' => '患者性别,1:男,0:女',
                                        'patient_age' => '患者年龄',
                                        'time' => '时间',
                                        'status' => '状态',
                                        'status_code' => '状态code',
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
                    '获取【我的接诊】详细信息' => [
                        'url' => $http . '/api/admissions/detail/{appointment_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'patient_demand' => [
                                'doctor_name' => '患者所需的医生姓名',
                                'hospital' => '患者所需的医院',
                                'department' => '患者所需的科室',
                                'job_title' => '患者所需的医生头衔'
                            ],
                            'basic_info' => [
                                'appointment_id' => '约诊ID',
                                'history' => '现病史',
                                'img_url' => '辅助检查',
                                'date' => '就诊时间',
                                'hospital' => '就诊医院',
                                'remark' => '补充说明',
                                'supplement' => '就诊须知'
                            ],
                            'doctor_info' => [
                                'id' => '用户ID; 这个是代约医生或平台的信息',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
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
                                ],
                                'status_code' => '状态CODE'
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
                    ],
                    '我的患者' => [
                        'url' => $http . '/api/patient/all',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'patient_count' => '患者总数量',
                                'appointment_count' => '约诊总数量',
                                'face_to_face_count' => '面诊总数量',
                                'patient_list' => [
                                    'id' => '患者ID',
                                    'name' => '患者姓名',
                                    'sex' => '患者性别',
                                    'age' => '患者年龄',
                                    'phone' => '患者电话',
                                    'avatar' => '患者头像',
                                    'appointment_count' => '患者约诊次数',
                                    'face_to_face_count' => '患者面诊次数'
                                ]
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
                    '全部已读' => [
                        'url' => $http . '/api/msg/admissions/all-read',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ]
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
                    '全部已读' => [
                        'url' => $http . '/api/msg/appointment/all-read',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '申请名片' => [
                    '提交申请' => [
                        'url' => $http . '/api/card/submit',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '再次提交' => [
                        'url' => $http . '/api/card/resubmit',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'HTTP状态204',
                        'response' => [
                            'success' => '',
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '查询' => [
                    '根据ID List查询医生列表' => [
                        'url' => $http . '/api/search/doctors',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id_list' => '医生ID list,ID之间用逗号分隔; 例如:1,3,4,5'
                        ],
                        'response' => [
                            'data' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                'city' => '所属城市',
                                'hospital' => [
                                    'id' => '用户所在医院ID',
                                    'name' => '用户所在医院名称'
                                ],
                                'department' => [
                                    'id' => '用户所在科室ID',
                                    'name' => '用户所在科室名称'
                                ],
                                'is_auth' => '是否认证；',
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
                                'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '根据医脉码查询医生信息' => [
                        'url' => $http . '/api/search/doctor_info',
                        'method' => 'POST',
                        'form-data' => [
                            'dp_code' => '纯数字号码'
                        ],
                        'response' => [
                            'data' => [
                                'is_friend' => 'true | false',
                                'id' => '用户id',
                                'code' => '医脉码',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称,直接传名称; 总共4个: 主任医师,副主任医师,主治医师,住院医师',
                                'province' => '用户所在省份名称',
                                'city' => '用户所在城市名称',
                                'hospital' => '用户所在医院名称',
                                'department' => '用户所在科室名称',
                                'college' => '用户所在院校名称',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证；未认证： ；认证成功：completed；认证中：processing；认证失败：fail；',
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
                ],

                '银行卡信息' => [
                    '银行卡信息列表' => [
                        '说明' => '返回该用户所有银行卡信息，有可能是空或多条数据',
                        'url' => $http . '/api/bank/info',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                [
                                    'id' => '数据ID',
                                    'name' => '银行名称',
                                    'info' => '银行开户行信息',
                                    'no' => '银行卡号',
                                    'verify' => '是否实名认证',
                                    'status' => '状态',
                                    'desc' => '描述/备注'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '新增银行卡信息' => [
                        '说明' => '成功增加后返回该用户所有银行卡信息，有可能是空或多条数据',
                        'url' => $http . '/api/bank/new',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'name' => '银行名称； 必填',
                            'info' => '银行开户行信息',
                            'no' => '银行卡号； 必填',
                            'desc' => '描述/备注'
                        ],
                        'response' => [
                            'data' => [
                                [
                                    'id' => '数据ID',
                                    'name' => '银行名称',
                                    'info' => '银行开户行信息',
                                    'no' => '银行卡号',
                                    'verify' => '是否实名认证',
                                    'status' => '状态',
                                    'desc' => '描述/备注'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '更新银行卡信息' => [
                        '说明' => '成功更新后返回该用户所有银行卡信息，有可能是空或多条数据',
                        'url' => $http . '/api/bank/update',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '数据ID； 必填',
                            'name' => '银行名称； 必填',
                            'info' => '银行开户行信息',
                            'no' => '银行卡号； 必填',
                            'desc' => '描述/备注'
                        ],
                        'response' => [
                            'data' => [
                                [
                                    'id' => '数据ID',
                                    'name' => '银行名称',
                                    'info' => '银行开户行信息',
                                    'no' => '银行卡号',
                                    'verify' => '是否实名认证',
                                    'status' => '状态',
                                    'desc' => '描述/备注',
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '删除银行卡信息' => [
                        '说明' => '成功更新后返回该用户所有银行卡信息，有可能是空或多条数据',
                        'url' => $http . '/api/bank/delete',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '数据ID； 必填'
                        ],
                        'response' => [
                            'data' => [
                                [
                                    'id' => '数据ID',
                                    'name' => '银行名称',
                                    'info' => '银行开户行信息',
                                    'no' => '银行卡号',
                                    'verify' => '是否实名认证',
                                    'status' => '状态',
                                    'desc' => '描述/备注',
                                ]
                            ],
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
