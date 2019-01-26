<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    //用户获取token
    'user/getToken' =>'user/user/decrypt',
    //用户更新信息--第一次登录
    'user/upUserInfo'  =>'user/user/upUserInfo',
    //用户获取信息
    'user/getUserInfo' =>'user/user/getUserInfo',
    //用户分享
    'user/addShareTimes' => 'users/User/addShareTimes',
    //发红包的支付
    'redpacket/create' => 'redpacket/Redpacket/create',
    //支付成功回调
    'redpacket/success' => 'redpacket/Redpacket/pay_success',
    //获取上传七牛token
    'audio/getUploadToken' => 'redpacket/Audio/getUploadToken',
    //获取红包信息
    'redpacket/getRedInfo/:id' => ['redpacket/Redpacket/getRedInfo',['method'=>'get'],['id'=>'\d+']],
    //我的记录
    'user/record'      => 'users/user/record',
    'user/record_list' => 'users/user/record_list',
    //获取上传音频token
    'audio/uploadtoken'=> 'redpacket/Qiniu/getUploadToekn',
    //推广红包
    'red/adRed' => 'redpacket/Redpacket/adRedPacket',
    //获取access_token
    'user/getAccessToken' => 'users/User/ac_token',
    //红包详情
    'user/details_list' => 'users/user/details_list',
    //红包识别
    'redpacket/recognise' => 'redpacket/Redpacket/recognise',
    //预设口令
    'message/preset'     => 'message/preset/preset',
    //显示弹幕
    'message/barrage'    => 'message/preset/barrage',
    //修改弹幕
    'message/up_barrage' => 'message/preset/update_barrage',
    //弹幕开关
    'message/is_barrage' => 'message/preset/is_barrage',
    //获取我的语音
    'users/getMyVoice'  => 'users/user/getMyVoice',
    //问题列表
    'message/problem_list'   => 'message/problem/problemList',
    //发出 红包明细
    'detail/send'       => 'detail/detail/sendDetail',
    //收包明细
    'detail/receive'    => 'detail/detail/receiveDetail',
    //提现明细
    'detail/cash'       => 'detail/detail/cashDetail',
    //是否推送
    'message/is_push'   => 'message/message/is_push',
    //比率
    'message/getProportion'   => 'message/preset/getProportion',
    //模板信息
    'message/template'        => 'message/preset/template',

    //上传received音频路径
    'redpacket/receive_voice' => 'redpacket/Redpacket/voice_url',
    //提现申请
    'cash/create' => 'redpacket/Cash/cashCreate',

    //听语音领红包
    'redpacket/listen' => 'redpacket/Redpacket/listen',

    //举报
    'users/report'          => 'users/user/report',

    //swoole测试
    'swoole/test' => 'myswoole/Myswoole/test',



];
