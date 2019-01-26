<?php
namespace anu;


class PostManTest
{
    //获得一个随机的手机号码
    public function getRandomPhone($num=1)
    {
        $arr = array(
            130,131,132,133,134,135,136,137,138,139,
            144,147,
            150,151,152,153,155,156,157,158,159,
            176,177,178,
            180,181,182,183,184,185,186,187,188,189,
        );
        for($i = 0; $i < $num; $i++) {
            $tmp[] = $arr[array_rand($arr)].''.mt_rand(1000,9999).''.mt_rand(1000,9999);
        }
        if(count($tmp)==1)return $tmp[0];
        return $tmp;
    }

    //获得一个经营范围
    public function getMangerScope($key=false)
    {
        $array = [
            '周边商超','外卖', '美食','维修','跑腿','团购'
        ];
        if($key){
            return isset($array[$key])?$array[$key]:'key 只能为 0-5 ';
        }else{
            return $array;
        }

    }
}