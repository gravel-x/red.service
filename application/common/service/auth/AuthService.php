<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/30
 * Time: 13:34
 */

namespace app\common\service\auth;

use app\common\model\RelationModel;
use app\common\service\PushService;
use redis\RedisClient;

class AuthService extends PushService
{

    /**
     * 解密token
     * @param $payload
     * redis = user_id.$user_id => $openid ,$exptime
     * $user
     */
    public static function validateToken($payload)
    {

        $user_id = $payload->user_id;
        $redis = RedisClient::getHandle(0);
        $rest = $redis->getValueTime('user_id:'.$user_id);

        //当键不存在，$rest 返回false
        if ($rest == -2) {
            $map['user_id'] = $user_id;
            $res = RelationModel::relationInfo($map,'openid');

            if (empty($res)) {
                self::pushError(1001,'用户不存在!');
            }else{
                $redis->setKey('user_id:'.$user_id,$res['openid']);
            }
        }

        return $user_id;
    }

}