<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/8/18
 * Time: 下午5:00
 */
namespace App\Api\Helper;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class SaveImage
{
    /**
     * 存储头像文件并压缩成150*150
     *
     * @param $filename
     * @param $file
     * @return string
     */
    public static function avatar($filename, $file)
    {
        $domain = \Config::get('constants.DOMAIN');
        $destinationPath = \Config::get('constants.AVATAR_SAVE_PATH');
        $suffix = '.png';
        $filename = $filename . $suffix;
        $mark = '?v=' . time(); //修改URL

        try {
            $file->move($destinationPath, $filename);
            Image::make($destinationPath . $filename)->fit(150)->save();
        } catch (\Exception $e) {
            Log::info('save-img-avatar', ['context' => $e->getMessage()]);
        }

        return $domain . '/' . $destinationPath . $filename . $mark;
    }

    /**
     * 保存认证图片
     *
     * @param $dirName
     * @param $imgFile
     * @param int $count
     * @return string
     */
    public static function auth($dirName, $imgFile, $count = 0)
    {
        $domain = \Config::get('constants.DOMAIN');
        $destinationPath = \Config::get('constants.AUTH_PATH') . $dirName . '/';
        $suffix = '.png';
        $filename = time() + $count . $suffix;
        $fullPath = $destinationPath . $filename;
        $newPath = str_replace($suffix, '_thumb.png', $fullPath);

        try {
            $imgFile->move($destinationPath, $filename);
            Image::make($fullPath)->encode('png', 30)->save($newPath); //按30的品质压缩图片
        } catch (\Exception $e) {
            Log::info('save-img-auth', ['context' => $e->getMessage()]);
        }

        return $domain . '/' . $newPath;
    }

    /**
     * 保存约诊图片
     *
     * @param $dirName
     * @param $file
     * @return string
     */
    public static function appointment($dirName, $file)
    {
        $domain = \Config::get('constants.DOMAIN');
        $destinationPath = \Config::get('constants.CASE_HISTORY_SAVE_PATH') . date('Y') . '/' . date('m') . '/' . $dirName . '/';
        $suffix = '.png';
        $filename = time() . $suffix;
        $fullPath = $destinationPath . $filename;
        $newPath = str_replace($suffix, '_thumb.png', $fullPath);

        try {
            $file->move($destinationPath, $filename);
            Image::make($fullPath)->encode('png', 30)->save($newPath); //按30的品质压缩图片
        } catch (\Exception $e) {
            Log::info('save-img-appointment', ['context' => $e->getMessage()]);
        }

        return $domain . '/' . $newPath;
    }
}
