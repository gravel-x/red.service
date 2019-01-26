<?php

/** Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 15:12
 */
namespace app\detail\service;
use app\common\model\RedpacketModel;
use think\Db;
use app\common\model\ReceivedModel;
use app\common\model\SendModel;
use app\common\service\PushService;
use app\redpacket\model\CashLogModel;

class DetailService extends PushService
{

    //发出红包明细
    public static function sendDetail($user_id)
    {
        $res = RedpacketModel::findDatas(['user_id' => $user_id, 'is_pay' => 1],'rp_id,user_id,rp_money,create_time');
        if (empty($res)) {
            self::pushError(500, '暂无数据');
        } else {
            return $res;
        }
    }


    //收取红包明细
    public static function receiveDetail($user_id)
    {
        $res = ReceivedModel::receiveDetail(['user_id' => $user_id], 'user_id,received_money,create_time');
        if (empty($res)) {
            self::pushError(500, '暂无数据');
        } else {
            return $res;

        }
    }

    //提现明细
    public static function cashDetail($user_id)
    {
        $res = CashLogModel::cashDetail(['user_id' => $user_id], 'user_id,cash_money,create_time');
        if (empty($res)) {
            self::pushError(500, '暂无数据');
        } else {
            return $res;
        }
    }

    //退款明细
    public static function refund($uid)
    {

        $res = Db::name('refund')->where('user_id', $uid)->field('refund_money,refund_time')->order('refund_time desc')->limit(10)->select();
        if ($res) {
            return $res;
        } elseif (empty($res)) {
            self::pushError( 500,  '暂无数据');
        } else {
            self::pushError(500,'服务器忙，请稍候在试');
        }
    }

    //发出红包列表
    public static function send_red_list($data)
    {
        $validate = validate('MyDetail');
        if (!$validate->check($data)) {
            self::pushError(4004,$validate->getError());
        }

        $start_page = ($data['page'] <= 1) ? 1 : ($data['page'] - 1) * 10;

        //搜索名称
        if (!empty($data['user_name'])) {
            $showData['total'] = Db::name('send')
                ->alias('s')
                ->join('wj_users u', 's.user_id = u.user_id')
                ->where('u.user_name', 'like', '%' . $data['user_name'] . '%')
                ->count();
            $showData['list'] = Db::name('send')
                ->alias('s')
                ->join('wj_users u', 's.user_id = u.user_id')
                ->order('create_time desc')
                ->where('u.user_name', 'like', '%' . $data['user_name'] . '%')
                ->limit($start_page, 10)
                ->field('u.user_name,s.user_id,s.red_id,s.se_money,s.se_number,s.content,s.voice,s.is_pay,s.type,s.qr_url,s.create_time')
                ->select();
            if (empty($showData['list'])) {
                self::pushError(500,'没有找到相关数据');
            }
            return $showData;
        }
        //搜索时间
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $showData['total'] = Db::name('send')->where('create_time', 'between', [$data['start_time'], $data['end_time']])->count();
            $showData['list'] = Db::name('send')
                ->alias('s')
                ->join('wj_users u', 's.user_id = u.user_id')
                ->where('create_time', 'between', [$data['start_time'], $data['end_time']])
                ->order('create_time desc')
                ->limit($start_page, 10)
                ->field('u.user_name,s.user_id,s.red_id,s.se_money,s.se_number,s.content,s.voice,s.is_pay,s.type,s.qr_url,s.create_time')
                ->select();
            if (empty($showData['list'])) {
                self::pushError(500,'没有找到相关数据');
            }
            return $showData;
        }

        //支付筛选 1支付 0未支付
        if (!empty($data['is_pay'])) {
            $is_pay = $data['is_pay'] == 1 ? 1 : 0;
            $showData['total'] = Db::name('send')->where('is_pay', $is_pay)->count();
            $showData['list'] = Db::name('send')
                ->alias('s')
                ->join('wj_users u', 's.user_id = u.user_id')
                ->where('is_pay', $is_pay)
                ->order('create_time desc')
                ->limit($start_page, 10)
                ->field('u.user_name,s.red_id,s.user_id,s.se_money,s.se_number,s.pay_money,s.voice,s.is_pay,s.type,s.content,s.qr_url,s.create_time')
                ->select();
            if (empty($showData['list'])) {
                self::pushError(500,'没有找到相关数据');
            }
            return $showData;
        }

        $showData['total'] = Db::name('send')->count();
        $showData['list'] = Db::name('send')
            ->alias('s')
            ->join('wj_users u', 's.user_id = u.user_id')
            ->order('create_time desc')
            ->limit($start_page, 10)
            ->field('u.user_name,s.red_id,s.user_id,s.se_money,s.se_number,s.pay_money,s.voice,s.is_pay,s.type,s.content,s.qr_url,s.create_time')
            ->select();
        return $showData;
    }

    //收包列表
    public static function received_red_list($data)
    {
        $validate = validate('MyDetail');
        if (!$validate->check($data)) {
            self::pushError(4004,$validate->getError());
        }
        $start_page = $data['page'] <= 1 ? 1 : ($data['page'] - 1) * 10;

        //搜索名称
        if (!empty($data['user_name'])) {
            $showData['total'] = Db::name('received')
                ->alias('r')
                ->join('wj_users u', 'r.user_id = u.user_id')
                ->where('u.user_name', 'like', '%' . $data['user_name'] . '%')
                ->count();
            $showData['list'] = Db::name('received')
                ->alias('r')
                ->join('wj_users u', 'r.user_id = u.user_id')
                ->order('create_time desc')
                ->where('u.user_name', 'like', '%' . $data['user_name'] . '%')
                ->limit($start_page, 10)
                ->field('u.user_name,r.user_id,r.red_id,r.re_money,r.voice_url,r.red_num,r.create_time')
                ->select();
            if (empty($showData['list'])) {
                self::pushError(500,'没有找到相关数据');
            }
            return $showData;
        }

        //时间搜索
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $showData['total'] = Db::name('received')->where('create_time', 'between', [$data['start_time'], $data['end_time']])->count();
            $showData['list'] = Db::name('received')
                ->alias('r')
                ->join('wj_users u', 'r.user_id = u.user_id')
                ->order('create_time desc')
                ->where('create_time', 'between', [$data['start_time'], $data['end_time']])
                ->limit($start_page, 10)
                ->field('u.user_name,r.user_id,r.red_id,r.re_money,r.voice_url,r.red_num,r.is_success,r.create_time')
                ->select();
            if (empty($showData['list'])) {
                self::setError(['status_code' => 500, 'message' => "没有找到相关数据!"]);
                return false;
            }
            return $showData;
        }

        $showData['total'] = Db::name('received')->count();
        $showData['list'] = Db::name('received')
            ->alias('r')
            ->join('wj_users u', 'r.user_id = u.user_id')
            ->order('create_time desc')
            ->limit($start_page, 10)
            ->field('u.user_name,r.user_id,r.red_id,r.re_money,r.voice_url,r.red_num,r.is_success,r.create_time')
            ->select();
        return $showData;
    }
}