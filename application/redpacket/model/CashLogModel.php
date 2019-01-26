<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 10:25
 */

namespace app\redpacket\model;

use think\Model;

class CashLogModel extends Model
{
    protected $name = 'cash';

    public static function getCashLog($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->find();
        return $res;
    }

    //获取今日提现记录
    public static function getTodayRecord($user_id)
    {
        $today_start = strtotime(date("Y-m-d"),time());
        $today_end = $today_start + 24*60*60;
        $res = self::where('user_id',$user_id)->where('create_time','between',[$today_start,$today_end])->count();

        return $res;
    }

    public static function addLog($addData)
    {
        $res = self::insert($addData);
        return $res;
    }

    public static function cashDetail($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->order('create_time desc')->limit(10)->select();
        return $res;
    }
}