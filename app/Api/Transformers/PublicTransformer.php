<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

class PublicTransformer
{
    /**
     * 多个期望就诊时间格式转换。
     * 
     * @param $dates
     * @param $am_pm
     * @return string
     */
    public static function expectVisitDateTransform($dates, $am_pm)
    {
        if ($dates == 0 || $dates == '' || $dates == null) {
            $expectVisitDate = '由专家决定约诊时间';
        } else {
            $expectVisitDateArr = explode(',', $dates);
            $expectVisitAmPmArr = explode(',', $am_pm);
            $expectVisitDate = '';
            for ($i = 0; $i < count($expectVisitDateArr); $i++) {
                $expectVisitDate .= $expectVisitDateArr[$i] . ' ' . (($expectVisitAmPmArr[$i] == 'am') ? '上午' : '下午');
                $expectVisitDate .= ',';
            }
            $expectVisitDate = substr($expectVisitDate, 0, strlen($expectVisitDate) - 1);
        }
        
        return $expectVisitDate;
    }

    /**
     * 我的约诊列表上显示的时间。
     * 
     * @param $appointment
     * @return string
     */
    public static function generateTreatmentTime($appointment)
    {
        if ($appointment['new_visit_time'] != '0000-00-00') {
            $retData = $appointment['new_visit_time'] . ' ' . (($appointment['new_am_pm'] == 'am') ? '上午' : '下午');
        } elseif ($appointment['visit_time'] != '0000-00-00') {
            $retData = $appointment['visit_time'] . ' ' . (($appointment['am_pm'] == 'am') ? '上午' : '下午');
        } elseif ($appointment['expect_visit_date'] == '0000-00-00' || $appointment['expect_visit_date'] == '' || $appointment['expect_visit_date'] == null) {
            $retData = '由专家决定约诊时间';
        } else {
            $retData = self::expectVisitDateTransform($appointment['expect_visit_date'], $appointment['expect_am_pm']);
        }

        return $retData;
    }
}
