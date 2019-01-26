<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/17
 * Time: 15:53
 */
namespace app\users\model;
use think\Model;

class ReportModel extends Model
{
    protected $name = 'report';

    public  static  function insertReport(array $data)
    {
        return self::insert($data);
    }


}