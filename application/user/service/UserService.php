<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/30
 * Time: 17:40
 */
namespace app\user\service;

use app\common\model\UserInfoModel;
use app\common\service\CurlService;
use app\common\service\PushService;
use Firebase\JWT\JWT;
use redis\RedisClient;
use app\common\model\RelationModel;
use app\common\model\UserModel;
use wechat\WXBizDataCrypt;


class UserService extends PushService
{

    /**
     * @param $privately 根据前端传递的edition
     * @param $data  前端传递的code
     * @return mixed 需要给前端返回token
     */

    public static function decrypt($privately,$data){


        if (!isset($privately['appId']) || !isset($privately['secret']) || !isset($data['code'])) {
            self::pushError(3001,'缺少关键值!');
        }

        $redis = RedisClient::getHandle(0);
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$privately['appId'].'&secret='.$privately['secret'].'&js_code='.$data['code'] .'&grant_type=authorization_code';
        $res = json_decode(CurlService::cUrl($url),true);

        //判断用户是否存在
        $sentence['openid']  = $res['openid'];

        $relationInfo = RelationModel::relationInfo($sentence,'user_id,relation_id');

        if (empty($relationInfo)) {
            //用户不存在，第一次登录
            $emptyInfo['user_openid'] = $res['openid'];
            $emptyInfo['user_name'] = '';
            $emptyInfo['user_icon'] = '';
            $emptyInfo['form_id']   = '';
            $emptyInfo['share_times']  = 0;
            $emptyInfo['is_del']  = '';
            $userId = UserInfoModel::addEmpty($emptyInfo);

            $relationData['user_id'] = $userId;
            $relationData['project_id'] = $privately['projectId'];
            $relationData['openid'] = $res['openid'];
            $relationId = RelationModel::addRelation($relationData);

            if (!$relationId){
                self::pushError(2003,'数据操作失败！');
            }

            $redis->setKey('openid:'.$res['openid'],$res['session_key']);
            $payload['user_id'] = $userId;
            $payload['exp']     = time()+6666000;
            $result['token']  = JWT::encode($payload,config('jwt-key'));

            return $result;

        }else{

            //如果存在，则更新token和session_key
            $payload['user_id'] = $relationInfo['user_id'];
            $payload['exp']  = time()+6666000;
            $result['token'] = JWT::encode($payload,config('jwt-key'));

            $redis->setKey('openid:'.$res['openid'],$res['session_key']);

            return $result;
        }
    }



    //更新用户信息
    public static function upUserInfo($privately,$data){

        //encryptedData解密，验证数据的真实性
        $redis = RedisClient::getHandle(0);
        $openid = $redis->getKey('user_id:'.$privately['user_id']);

        $session_key = $redis->getKey('openid:'.$openid);

        $pc = new WXBizDataCrypt($privately['appId'],$session_key);
        $errCode = $pc->decryptData($data['encryptedData'],$data['iv'],$newData);

        if ($errCode == 0) {
            $userData = json_decode($newData,true);

            $userInfo = $redis->getKey('userinfo:'.$openid);

            if ($userInfo == true || $userInfo != $userData['nickName'].$userData['avatarUrl']) {

                $redis->setKey('userinfo:'.$openid,$userData['nickName'].$userData['avatarUrl']);

                $condition['user_openid'] = $openid;
                $updata['user_name']   = $userData['nickName'];
                $updata['user_icon']   = $userData['avatarUrl'];
                $is_success = UserInfoModel::changData($condition,$updata);

                if ($is_success != 1) {
                    self::pushError('3001','更新数据失败！');
                }
                return true;
            }else{
                return true;
            }

        }else{
            self::pushError('3001',$errCode);
        }
    }


    //获取用户信息
    public static function getUserInfo($user_id)
    {
        $redis = RedisClient::getHandle(0);
        $openid = $redis->getKey('user_id:'.$user_id);

        $userData = UserInfoModel::getUserInfo(['user_openid'=>$openid],'share_times,user_balance');
        if(empty($userData)){
            self::pushError(500,'网络错误！');
        }

        return $userData;
    }
}