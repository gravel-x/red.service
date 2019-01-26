<?php

namespace app\common\service;

use think\Controller;
use think\Log;

/**
 * @author: helei
 * @createTime: 2016-07-20 18:31
 * @description:
 */

/**
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class TestNotify
 * anthor helei
 */

class RedAllotService extends Controller
{
    public static function getRedArray($money, $num, $type)
    {
        //随机红包
        if ($type == 1) {
            $money_arr = self::redMoneys($money,$num);

        } else{
            //平均分配

        }
        return $money_arr;
    }

    //随机红包分配
    public static function randFloat($min,$max)
    {
        $num = $min+mt_rand()/mt_getrandmax()*($max-$min);
        return sprintf("%2f",$num);
    }
    public static function redMoneys($total,$num){
        $res = [];
        $i=1;
        while($num>0){
            $i++;
            if($num ==1 ){
                $num--;
                array_push($res,$total);
            }else{
                $min = 0.01;
                $max = $total/$num*2;
                $money =  sprintf('%2f',self::randFloat(0,1)*$max);
                if($money<=$min){
                    $money = 0.01;
                }
                $money = floor($money*100)/100;

                $num-=1;
                $total = (float)bcsub($total,$money,2);
                array_push($res,$money);
            }
        }
        return $res;
    }
}
