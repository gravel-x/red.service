<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/11
 * Time: 17:41
 */

namespace app\redpacket\service;

use app\common\model\ReceivedModel;
use app\common\model\RedpacketModel;
use app\common\model\UserInfoModel;
use Overtrue\Pinyin\Pinyin;
use app\common\service\PushService;
use Payment\Common\PayException;
use Payment\Client\Charge;
use Payment\Config;
use redis\RedisClient;
use app\common\model\ExtensionModel;
use app\common\service\CurlService;
use app\users\service\UserService;
use think\Log;

class RedpacketService extends PushService
{
    public static function create_redpacket($privately, $data)
    {
        date_default_timezone_set('Asia/Shanghai');
        $redis = RedisClient::getHandle(0);
        //阿拉伯数字转中文数字
        $content = AudioService::chinanum($data['content']);
        $data['content'] = implode('', $content);

        $order_sn = 'WJBS' . date('YmdHis') . rand(1000, 9999);

        $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
        $content_pinyin = implode(',', $pinyin->convert($data['content']));

        $save_data = [
            'user_id' => $privately['user_id'],
            'is_pay' => 0,
            'is_end' => 0,
            'rp_content' => $data['content'],
            'rp_type' => $data['type'],
            'rp_money' => $data['pay_money'],
            'rp_balance' => $data['pay_money'],
            'rp_num' => $data['send_number'],
            'rp_surplus' => $data['send_number'],
            'rp_voice' => $data['voice_url'] ?? null,
            'order_sn' => $order_sn,
            'rp_pinyin' => $content_pinyin,
            'create_time' => time(),
        ];

        $rp_id = RedpacketModel::addData($save_data);

        $proprotion = config('proprotion');
        if ($proprotion['item']) {
            $total = $data['pay_money'];
        } else {
            $total = 0.01;
        }

        $wxConfig = config('wxpay');

        $openid = $redis->getKey('user_id:' . $privately['user_id']);

        //统一下单
        $payData = [
            'body' => '拜年智力',
            'subject' => '微聚',
            'order_no' => $order_sn,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount' => $total,// 微信沙箱模式，需要金额固定为3.01,$money||$data['pay_money']
            'return_param' => '123',
            'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
            'openid' => $openid,
            'product_id' => '123',
        ];

        try {
            $ret = Charge::run(Config::WX_CHANNEL_LITE, $wxConfig, $payData);
            $ret['rp_id'] = $rp_id;
            return $ret;
        } catch (PayException $e) {
            self::pushError(500, $e->errorMessage());
        }

    }

    public static function read($red_id, $uid)
    {
        $redInfo = RedpacketModel::findData(['rp_id' => $red_id, 'is_pay' => 1], 'rp_content,rp_type,rp_money,rp_balance,rp_num,rp_surplus,rp_voice,rp_code_url');

        if (!empty($redInfo)) {
            //查看用户是否已经领取
            $redis = RedisClient::getHandle(0);

            if ($redis->in_set('red_package:' . $red_id, $uid)) {
                $redInfo['is_revice'] = true;
                $re_money =  ReceivedModel::findData(['rp_id' => $red_id, 'user_id' => $uid], 'received_money');
                $redInfo['re_money'] = $re_money['received_money'];
            } else {
                $redInfo['is_revice'] = false;
                $redInfo['re_money'] = 0;
            }
            if ($redis->in_set('adv_reds', $red_id)) {
                $redInfo['is_ad'] = true;
            } else {
                $redInfo['is_ad'] = false;
            }
            $userInfo = UserInfoModel::getUserInfo(['user_id' => $uid], 'user_name,user_icon');
            $redInfo['user_name'] = $userInfo['user_name'];
            $redInfo['user_icon'] = $userInfo['user_icon'];
            return $redInfo;
        } else {
            self::pushError(500, '访问不存在！');
        }
    }

    public static function adRedPacket()
    {
        $res = ExtensionModel::getInfo('', 'rp_id,picture_url,is_show');
        $resData['arr'] = [$res];
        $resData['duration'] = 100;
        $resData['interval'] = 3000;
        return $resData;
    }

    public static function recognise($privately, $data)
    {
        /*
        * 判断是否已经抢过了
        */
        $redis = RedisClient::getHandle(0);

//        $is_get = $redis->in_set('red_package:'.$data['rp_id'],$privately['user_id']);
//
//        if ($is_get) {
//            self::pushError(500, '您已经领取过了！');
//        }

        $access_token = UserService::ac_token();
        $url = 'http://api.weixin.qq.com/cgi-bin/media/voice/queryrecoresultfortext';
        $curlData['access_token'] = $access_token['access_token'];
        $curlData['voice_id'] = $data['voice_id'];
        $res = CurlService::cUrl($url, '', $curlData);
        $res = json_decode($res,true);

        $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
        $content_pinyin = implode(',', $pinyin->convert($res['result']));

        $redpacketData = RedpacketModel::findData(['rp_id' => $data['rp_id'],'is_pay' => 1],'rp_pinyin,is_end,rp_balance,is_extension,rp_surplus');

        $userInfo = UserInfoModel::getUserInfo(['user_id' => $privately['user_id'],'is_del' => 0],'share_times,user_balance');

        if (empty($redpacketData)) {
            self::pushError(500, '红包不存在！');
        }

        $recognise = self::judge($content_pinyin, $redpacketData['rp_pinyin']);

        //识别成功
        if ($recognise) {
            //判断是不是广告红包
            if ($redpacketData['is_extension']) {
                //广告红包
                if ($userInfo['share_times'] <= 0) {
                    self::pushError(500, '请转发获取领取机会！');
                }
                $type = 1;//config('protion');

                if ($type == 1) {
                    $isset_time = $redis->getKey('ad_redpacket_time' . $data['rp_id']);
                    if ($isset_time) {
                    //领取广告红包时间未到
                        self::pushError(500, '领取失败！');
                    }
                } else {
                    $goalnum = config('goalnum') ?? 100;
                    $key = $redis->keyIncr('ad_redpacket_num:' . $data['rp_id']);

                    if ($key % $goalnum != 0) {
                        self::pushError(500, '领取失败！');
                    }

                }
            }

            $money = $redis->popList('rp_money:' . $data['rp_id']);

            if (!$money) {
                self::pushError(500, '已经被领取完毕！');
            }

            $save_data['user_id'] = $privately['user_id'];
            $save_data['rp_id'] = $data['rp_id'];
            $save_data['received_money'] = $money;
            $save_data['received_voice'] = '';
            $save_data['create_time'] = time();
            $save_data['voice_length'] = '';

            ReceivedModel::addData($save_data);

            //计算余额
            $user_balance = bcadd($userInfo['user_balance'], $money, 2);
            UserInfoModel::changData(['user_id' => $privately['user_id']], ['user_balance' => $user_balance]);
            $redis->add_set('red_package:'.$data['rp_id'],$privately['user_id']);

            RedpacketModel::setData(['rp_id'=>$data['rp_id']],['rp_surplus'=>($redpacketData['rp_surplus']-1),'rp_balance'=>($redpacketData['rp_balance']-$money)]);

            return [
                'money' => $money,
            ];

        } else {
            self::pushError(500, '领取失败，请重试！');
        }
    }

    public static function judge($content_a, $content_b)
    {
        //判断两者相似度
        $a = explode(',', $content_a);
        $b = explode(',', $content_b);
        if (count($a) != count($b)) {
            return false;
        }

        $rate = 80;
        $rate = (float)$rate / 100;
        $c = array_intersect($a, $b);

        $rateReal = count($c) / count($b);

        if ($rateReal >= $rate) {
            return true;
        } else {
            return false;
        }
    }

    public static function voice_url($privately,$data)
    {
        $condition['user_id'] = $privately['user_id'];
        $condition['rp_id']   = $data['rp_id'];

        $res = ReceivedModel::upData($condition,['received_voice'=>$data['voice_url'],'duration'=>$data['duration']]);

        if ($res) {
            return $res;
        }else{
            self::pushError(500,'网络错误！');
        }
    }

    public static function listen($privately,$data)
    {
        /*
        * 判断是否已经抢过了
        */
        $redis = RedisClient::getHandle(0);

        $is_get = $redis->in_set('red_package:'.$data['rp_id'],$privately['user_id']);

//        Log::write('is_get :' .$is_get);
//        if ($is_get) {
//            self::pushError(500, '您已经领取过了！');
//            Log::write('pushError 要加 die  +++++++++++++++++++');
//        }


        $redpacketData = RedpacketModel::findData(['rp_id' => $data['rp_id'],'is_pay' => 1],'rp_pinyin,is_end,rp_balance,is_extension,rp_surplus');

        $userInfo = UserInfoModel::getUserInfo(['user_id' => $privately['user_id'],'is_del' => 0],'share_times,user_balance');

        if (empty($redpacketData)) {
            self::pushError(500, '红包不存在！');
        }

        $money = $redis->popList('rp_money:' . $data['rp_id']);

        $save_data['user_id'] = $privately['user_id'];
        $save_data['rp_id'] = $data['rp_id'];
        $save_data['received_money'] = $money;
        $save_data['received_voice'] = '';
        $save_data['create_time'] = time();
        $save_data['voice_length'] = '';

        ReceivedModel::addData($save_data);

        //计算余额
        $user_balance = bcadd($userInfo['user_balance'], $money, 2);
        UserInfoModel::changData(['user_id' => $privately['user_id']], ['user_balance' => $user_balance]);
        $redis->add_set('red_package:'.$data['rp_id'],$privately['user_id']);

        RedpacketModel::setData(['rp_id'=>$data['rp_id']],['rp_surplus'=>($redpacketData['rp_surplus']-1),'rp_balance'=>($redpacketData['rp_balance']-$money)]);

        return [
            'money' => $money,
        ];

    }
}