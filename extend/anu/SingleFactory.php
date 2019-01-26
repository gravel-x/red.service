<?php
namespace anu;


class SingleFactory
{
    private static $object=[];
    private static $data=[];
    public static function getObject($className)
    {
        if(isset(self::$object[$className])){
            return self::$object[$className];
        }else{
            self::$object[$className] = new $className;
            return self::$object[$className];
        }
    }
    /**
     * 全空返回$data全部属性
     * 只传key，返回对应key的值
     * 两个都传，则执行全局赋值，返回原值
     */
    public static function overAllData($key=null,$value=null)
    {   
        if(empty($key)&&empty($value))return self::$data;
        if($value === null){
            if(isset(self::$data[$key]))return self::$data[$key];
            return false;
        }else{
            self::$data[$key]=$value;
            return self::$data[$key];
        }
    }
}