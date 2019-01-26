<?php
namespace app\common\service;
use app\common\model\RedpacketModel;
use app\common\model\RelationModel;
use Payment\Notify\PayNotifyInterface;
use Payment\Config;
use think\Db;
use redis\RedisClient;
use app\common\service\RedAllotService;
use app\users\service\UserService;
use think\Log;

/**
 * @author: helei
 * @createTime: 2016-07-20 18:31
 * @description:
 */

/**
 * 客户端需要继承该接口，并实现这个方法，在其中实现对应的业务逻辑
 * Class TestNotify
 * anthor helei
 */

class TestNotify implements PayNotifyInterface
{
    public function notifyProcess(array $data)
    {
        $channel = $data['channel'];
        if ($channel === Config::ALI_CHARGE) {// 支付宝支付
        } elseif ($channel === Config::WX_CHARGE) {// 微信支付
        } elseif ($channel === Config::CMB_CHARGE) {// 招商支付
        } elseif ($channel === Config::CMB_BIND) {// 招商签约
        } else {
            // 其它类型的通知
        }

        //获取用户信息
        $user_data = RelationModel::relationInfo(['openid'=>$data['openid']],'user_id');
        if (empty($user_data)) {
            return false;
        }

        //查询订单
        $condition = [
            'order_sn'=> $data['out_trade_no'],
            'user_id'=>$user_data['user_id']
            ];
        $order_info = RedpacketModel::findData($condition,'rp_money,rp_id,rp_num');

        //不存在订单
        if (empty($order_info)) {
            return false;
        }

        $redis = RedisClient::getHandle(0);
        //随机拆分红包
        $money_arr = RedAllotService::getRedArray($order_info['rp_money'], $order_info['rp_num'], 1);
        foreach ($money_arr as $k => $v) {
            $redis->pushList('rp_money:' . $order_info['rp_id'], $v);
        }

        //生成二维码
        $born_url = UserService::QR_code($order_info['rp_id']);

        if ($born_url != false) {
            $upData = [
                'is_pay' => 1,
                'rp_code_url' => $born_url
            ];
            //更新红包数据
            $is_up =  RedpacketModel::setData(['rp_id' => $order_info['rp_id']],$upData);

            if ($is_up) {
                return true;
            }
        }else{

            return false;

        }
    }
}