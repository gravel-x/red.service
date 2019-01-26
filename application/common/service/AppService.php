<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/2
 * Time: 14:28
 */

namespace app\common\service;

use app\common\model\VersionModel;
use think\Response;
use redis\RedisClient;

class AppService extends PushService
{
    /**
     * @param $project_id 项目标识，第几个项目，前端header定义
     * 根据项目标识获取appid，secret
     */
    public static function getAppInfo($project_id)
    {
        //链接redis
        $redis = RedisClient::getHandle(0);
        //根据 project_id 获取 redis缓存
        $res = $redis->getKey('project_id:'.$project_id);
        if (!$res) {
            $map['app_id'] = $project_id;
            $res = VersionModel::getEditon($map);
            $add_redis = $redis->add_set('project_id',$res['app_id'],$res['app_aid'],$res['app_secret']);
            if ($add_redis) {
                self::pushError(500,'缓存失败！');
            }
        }
        if (empty($res)) {
            self::pushError(1004,'该项目不存在!');
        }else{
            return $res;
        }
    }
}