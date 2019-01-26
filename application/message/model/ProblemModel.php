<?php
/**
 * Created by PhpStorm.
 * User: gravel
 * Date: 2018/4/14
 * Time: 13:45
 */
namespace app\message\model;
use think\Model;

class ProblemModel extends Model
{
    protected $name = 'questions';

    public static function getProblem()
    {
        return self::select();

    }

}