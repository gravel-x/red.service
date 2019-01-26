<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/5
 * Time: 15:54
 */
namespace app\redPacket\model;

use think\Model;

class QuestionsModel extends Model
{
    protected $name = 'questions';

    //获取所有问题
    public static function getQuestions()
    {
        return self::where('status = 1')->field('question_title,question_answer')->select();
    }

    //新增问题
    public static function addQuestions($addData)
    {
        self::saveAll($addData);
    }

}