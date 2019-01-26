<?php
/**
 * Created by PhpStorm.
 * User: gravel
 * Date: 2018/4/14
 * Time: 13:40
 */
namespace app\message\controller;
use app\common\controller\Middle;
use app\message\service\ProblemService;

class Problem extends Middle
{
    /**
     * 问题列表显示
     **/
    public function problemList()
    {
        $list = ProblemService::problemList();
        $this->pushSuccess($list);
    }


}