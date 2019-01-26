<?php
/**
 * Created by PhpStorm.
 * User: gravel
 * Date: 2018/4/14
 * Time: 13:41
 */
namespace app\message\service;
use app\common\service\PushService;
use app\message\model\ProblemModel;
use redis\RedisClient;

class ProblemService extends PushService
{
    /**
     * 常见问题列表
     */
    public static function problemList()
    {

        $redis  = RedisClient::getHandle(0);

        $redis_array = $redis->getKey('problem');

        if (empty($redis_array)) {
            $problem_data = ProblemModel::getProblem();
            if ($problem_data) {
                $redis->setKey('problem',json_encode($problem_data));
            }
        }else{
            $problem_data = json_decode($redis_array,true);
        }

        if (empty($problem_data)) {
            self::pushError(500,'暂无数据');
        }else{
            return $problem_data;
        }
    }
}