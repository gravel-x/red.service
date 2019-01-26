<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 10:03
 */

namespace app\redpacket\service;

use app\common\model\UserInfoModel;
use app\common\service\PushService;
use app\redpacket\model\CashLogModel;

use Payment\Common\PayException;
use Payment\Client\Transfer;
use Payment\Config;

class CashService extends PushService
{
    /**
     * 微信提现申请
     */
    public static function cash_apply($privately,$data)
    {
        //todo 以后把次数，记录，写入redis，便于后期逻辑优化。

        //验证提交的金额
        if (!is_numeric($data['cash_money'])) {
            self::pushError(500,'金额格式不正确');
        }

        date_default_timezone_set('Asia/Shanghai');

        $user_data = UserInfoModel::getUserInfo(['user_id'=>$privately['user_id']],'user_balance,user_openid');

        //频繁限制
        $last_cash = CashLogModel::getCashLog(['user_id'=>$privately['user_id']],'create_time');

        if (!empty($last_cash)) {
            $times = time()-$last_cash['create_time'];
            if ($times < 60) {
                self::pushError(500,'操作频繁！');
            }
        }

        //判断提现每日次数

        $day_times = CashLogModel::getTodayRecord($privately['user_id']);

        if ($day_times >= 3) {
            self::pushError(500,'每日提现次数不得超过3次！');
        }

        //计算剩余余额
        $new_balance = bcsub($user_data['user_balance'], $data['cash_money'], 2);
        if ($new_balance < 0) {
            self::pushError(500,'余额不足！');
        }


        //提现订单号
        $cash_no = 'WJTX' . date('YmdHis') . rand(1000, 9999);

        //获取后台配置项
        $proportion = config('proportion');

        //获取服务费比例
        $service = sprintf("%.2f", bcmul($data['cash_money'], $proportion['service'], 3));

        //数据准备
        $save_data = [
            'user_id'     => $privately['user_id'],
            'cash_money'  => $data['cash_money'],
            'cash_no'     => $cash_no,
            'trade_no'    => '',
            'create_time' => time(),
        ];

        //创建订单
        $saveCode = CashLogModel::addLog($save_data);

        if (!$saveCode) {
           self::pushError(500,'服务超时！');
        }

        //提现金额
        $cash_money = bcsub($data['cash_money'], $service, 2);
        $wxConfig = config('wxpay');
        //获取服务器ip
        $ip = gethostbyname($_SERVER['SERVER_NAME']);
        if ($wxConfig['is_open']) {
            $data = [
                'trans_no' => time(),
                'openid' => $user_data['user_openid'],
                'check_name' => 'NO_CHECK',// NO_CHECK：不校验真实姓名  FORCE_CHECK：强校验真实姓名   OPTION_CHECK：针对已实名认证的用户才校验真实姓名
                'payer_real_name' => '',
                'amount' => $cash_money,
                'desc' => '转账测试',
                'spbill_create_ip' => $ip,
            ];
            try {
                $ret = Transfer::run(Config::WX_TRANSFER, $wxConfig, $data);
                return ['message'=>'提现申请成功！'];
            } catch (PayException $e) {
                self::pushError(500,$e->errorMessage());
                exit;
            }
        } else {

            return true;

        }


    }
}