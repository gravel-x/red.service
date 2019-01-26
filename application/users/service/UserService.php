<?php
/**
 * User: gravel
 * Date: 2018/4/12
 * Time: 10:26
 */
namespace app\users\service;
use app\common\model\ExtensionModel;
use app\common\model\ReceivedModel;
use app\common\model\ShareModel;
use app\common\model\UserInfoModel;
use app\common\service\PushService;
use app\users\controller\User;
use app\users\model\Receive;
use app\users\model\ReportModel;
use redis\RedisClient;
use greatsir\Snowflake;
use wechat\WXBizDataCrypt;
use function GuzzleHttp\Psr7\str;
use Qiniu\Storage\UploadManager;
use think\Db;
use Firebase\JWT\JWT;
use think\Log;
use Qiniu\Auth;
class UserService extends PushService
{
    /*
     * 获取用户信息
     */
    public static function getUserInfo($data, $privately)
    {
        $userInfo = self::read($privately['user_id']);
        if (!$userInfo) {
            return false;
        }

        try {
            //校验用户信息
            $redis  = RedisClient::getHandle(0);
            $openid = $redis->getKey('user_id:'.$privately['user_id']);
            $session_key = $redis->getKey('openid:' . $openid);
            $appid = $privately['appId'];

            //解密数据，以及验证签名
            $pc = new WXBizDataCrypt($appid, $session_key);
            $errCode = $pc->decryptData($data['encryptedData'], $data['iv'], $newData);
            if ($errCode == 0) {
                //解密成功
                //更新用户信息
                $newData = json_decode($newData);
                $upData['user_name'] = $newData->nickName;
                $upData['user_icon'] = $newData->avatarUrl ;
//                $upData['user_unionid'] = $newData->unionId;

                $res = UserInfoModel::changData(['user_id' => $privately['user_id']],$upData);

                if ($res || $res == 0) {
                    $userInfo = self::read($privately['user_id']);
                    return $userInfo;
                }
            } else {
                self::pushError($errCode,'数据校验失败');
            }
        } catch (\Exception $e) {
            throw new \think\Exception($e->getMessage(), $e->getCode());
        }


    }

    //读取用户
    public static function read($privately)
    {
        $validate = validate('app\users\validate\User');
        if (!$validate->check(['user_id' => $privately['user_id']])) {
            self::pushError(4103,$validate->getError());
        }

        $where['user_id'] = $privately['user_id'];
        $where['is_del']  = 0;
        $userInfo = UserInfoModel::getUserInfo($where,'is_del');
        if (!empty($userInfo)) {
            return $userInfo;
        } else {
            self::pushError(404,'用户不存在');
        }
    }

    /**我的记录头部  1.收到和 2.发出
     * @param $type
     * @param $privately
     * @return mixed
     */

    public static function record($data, $uid)
    {
        $redis = RedisClient::getHandle(0);
        $openid = $redis->getKey('user_id:'.$uid);
        $user_msg = UserInfoModel::getUserInfo(['user_openid'=>$openid],'user_id,user_name,user_icon');

        if (!$user_msg) {
            self::pushError(500,'网络错误！');
        }
        $where['user_id'] = $uid;

        if ($data['type'] == 1) {
            $Db_name = 'received';
            $re      = 'received_money';
        } elseif ($data['type'] == 2) {
            $Db_name = 'redpacket';
            $re      = 'rp_money';
            $where['is_pay'] = 1;
        } else {
            self::pushError(4055,'请选择正确的记录');
        }
        $record = Db::name($Db_name)->field($re)->where($where)->select();
        if ($record) {
            $res = 0;
            foreach ($record as $k => $v) {
                $res = bcadd($res, $v[$re], 2);
            }
            $total['num'] = Db::name($Db_name)->where($where)->count();
            $total['money'] = $res;
        } else {
            $total['num'] = 0;
            $total['money'] = 0;
        }
        $total['user_name'] = $user_msg['user_name'];
        $total['user_icon'] = $user_msg['user_icon'];
        return $total;
    }


    /**我的记录底部 1.我收到的  2.我发出的
     * @param $type
     * @param $privately
     * @param $more
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function record_list($datas, $user_id)
    {

        $where['user_id'] = $user_id;
        if ($datas['type'] == 1) {
            $Db_name   = 'received';
            $re        = 'received_money';
            $field     = 'rp_id,received_money,create_time';
            $list_name = 'redpacket';
        } elseif ($datas['type'] == 2) {
            $Db_name   = 'redpacket';
            $re        = 'rp_money';
            $field     = 'rp_content,rp_money,create_time,rp_num,rp_id';
            $list_name = 'received';
            $where['is_pay'] = 1;
        } else {
            self::pushError(4055,'请选择正确的记录');
        }

        //显示条数
        if (empty($datas['page'] ) || $datas['page'] < 1) {
            $limit = 0;
        } else {
            $limit = ($datas['page'] - 1) * 5;
        }

        $data = Db::name($Db_name)->where($where)->field($field)->order('create_time desc')->limit($limit, 5)->select();

        foreach ($data as $k => $v) {
            if ($datas['type'] == 1) {
                $re      = Db::name($list_name)->where('rp_id', $v['rp_id'])->find();
                $getData = UserInfoModel::getUserInfo(['user_id'=>$re['user_id']],'user_name,user_icon');
                $data[$k]['send_user_name'] = $getData['user_name'];
                $data[$k]['send_user_icon'] = $getData['user_icon'];
            } else {
                $data[$k]['get_num'] = Db::name($list_name)->where('rp_id', $v['rp_id'])->count();
            }
        }

        if (!$data) {
            self::pushError(500,'没有记录');
        } else {
            foreach ($data as $k => $v) {
                $data[$k]['create_time'] = date('m-d H:i', $v['create_time']);
            }
            return $data;
        }

    }


    /**红包详情头部（点击记录后） 1.我收到的  2.我发出的
     * @param $type
     * @param $rp_id
     * @param $uid
     * @return mixed
     */
    public static function red_details($datas, $uid)
    {
        $my_msg = UserInfoModel::getUserInfo(['user_id'=>$uid],'user_name,user_icon,user_id');

        $send_msg = Db::name('redpacked')->where('rp_id', $datas['rp_id'])->find();
        if (!$send_msg) {
            self::pushError(4055,'没有该红包');
        }
        //1.听完声音领红包 2.口令红包  3.说答案领红包
        if ($send_msg['rp_type'] == 1) {
            $field = 'rp_voice';
            $data['re_type'] = 1;
        } elseif ($send_msg['rp_type'] == 2) {
            $field = 'rp_content';
            $data['re_type'] = 2;
        } elseif ($send_msg['rp_type'] == 3) {

            $field = 'rp_voice';
            $data['re_type'] = 3;
        } else {
            self::pushError(500,'红包种类有误');
        }
        if ($datas['type'] == 1) {
            $data['rp_money'] = ReceivedModel::getReceiveValue(['rp_id' => $datas['rp_id'], 'user_id' => $my_msg['user_id']],'received_money');
            $getData = UserInfoModel::getUserInfo(['user_id'=>$send_msg['user_id']],'user_name,user_icon');
            $data['send_user_name'] = $getData['user_name'];
            $data['send_user_icon'] = $getData['user_icon'];
        } elseif ($datas['type'] == 2) {
            $data['my_name'] = $my_msg['user_name'];
            $data['my_icon'] = $my_msg['user_icon'];
        } else {
            self::pushError(4005,'请选择发出或收到');
        }
        $data[$field] = $send_msg[$field];
        $se_red = Db::name('redpacket')->field('rp_money,rp_num')->where('rp_id', $datas['rp_id'])->find();
        $data['total_money']  = $se_red['rp_money'];
        $data['total_number'] = $se_red['rp_num'];
        $data['get_number']   = ReceivedModel::getReceiveCount(['rp_id'=>$datas['rp_id']]);
        return $data;
    }

    /**红包详情记录列表
     * @param $rp_id
     * @param $page
     * @return mixed
     */
    public static function details_list($datas)
    {
        if (empty($datas['page']) || $datas['page'] < 1) {
            $limit = 0;
        } else {
            $limit = ($datas['page']-1) * 5;
        }

        $data = ReceivedModel::getJoin(['rp_id'=>$datas['rp_id']],'b.user_name,b.user_icon,b.user_id,a.received_money,a.create_time,a.voice_length,a.rp_id,a.received_voice,a.duration',$limit);
        if(!empty($data)){
            return $data;
        }else{
            self::pushError(500,'没有数据');
        }
    }


    public static function AD_QR($red_id = null)
    {
        $ac_token = self::ac_token();
        if (!$red_id) {
            $red_id = '123';
        }
        $url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=" . $ac_token;//接口地址
        $data = [
            'path' => 'pages/recive/recive?red_id=' . $red_id,
            //'page' =>'pages/recive/recive',
            'width' => '50',

        ];

        $data = json_encode($data);
        $result = self::https_request($url, $data);//与接口建立会话

        if ($result) {
            $qr = self::add_qrimg($result, $red_id);
            return $qr;
        } else {
            return false;
        }
    }


    //二维码
    public static function QR_code($red_id = null)
    {
        $ac_token = self::ac_token();
        if (!$red_id) {
            $red_id = '123';
        }
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $ac_token['access_token'];//接口地址
        $data = [
            'scene' => $red_id,
            'page' => 'pages/recive/recive',
            'width' => '50',

        ];

        $data = json_encode($data);
        $result = self::https_request($url, $data);//与接口建立会话

        if ($result) {
            $qr = self::add_qrimg($result, $red_id);
            return $qr;
        } else {
            return false;
        }

    }

    //二进制转换为图片
    public static function add_qrimg($result, $red_id)
    {

        $accessKey = config('qiniu_accesskey');
        $secretKey = config('qiniu_secretKey');
        $bucket = config('qiniu_bucket');
        $qiniu = new Auth($accessKey, $secretKey);
        $noticeUrl = config('qiniu_notify');
        //上传策略
        $audioFormat = 'avthumb/wav/ab/16k';
        $policy = array(
            'persistentOps' => $audioFormat,
            'persistentPipeline' => "audio-pipe",
            'persistentNotifyUrl' => $noticeUrl,
        );
        $upToken = $qiniu->uploadToken($bucket);
        $upload = new UploadManager();
        $key = time() . $red_id;
        $re = $upload->put($upToken, $key, $result);

        $filePath = config('qiniu_bucket_domain') . DS . $re[0]['key'];
        //var_dump($re);die;
        return $filePath;

    }


    //连接微信接口
    public static function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    //获取AC——token值
    public static function ac_token()
    {
        $redis = RedisClient::getHandle(0);
        $ac_token = $redis->getKey('wechat_access_token');

        if (!$ac_token) {
            $ac_token = self::getAccess_token();
            $redis->setKey('wechat_access_token', $ac_token, 7000);

        }
        $times = $redis->getValueTime('wechat_access_token');
        $exptime = time()+$times;
        $res_array = ['access_token'=>$ac_token,'exptime'=>$exptime];
        return $res_array;

    }

    public static function getAccess_token()
    {
        $app_id = config('wechatapp_id');
        $secret = config('wechatapp_secret');
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $app_id . '&secret=' . $secret;
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_URL, $url);//与url建立对话
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //进行配置
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //进行配置
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//进行配置
        $output = curl_exec($ch);//执行对话，获取接口数据Access Token
        curl_close($ch);//关闭会话
        $jsoninfo = json_decode($output, true);//解码接口数据，将json格式字符串转换成php变量或数组。默认是变量，加true后是数组。

        $access_token = $jsoninfo["access_token"];
        if ($access_token) {
            return $access_token;
        } else {
            self::pushError(500,'网络错误！');
        }

    }



    /**获取我的语音
     * 1.听完声音领红包 2.口令红包  3.说答案领红包
     * @param $info   page
     * @param $uid
     * @return array
     */
    public static function getMyVoice($info,$uid)
    {

        if (empty($info['page']) || $info['page'] <= 1) {
            $limit = 0;
        } else {
            $limit = ($info['page']-1) * 5;
        }

        $condition = [
            'a.user_id'     =>$uid,
            'received_voice'=>array('neq',''),
        ];

        $res =  ReceivedModel::getJoinRedpacket($condition,$limit);

        $data = [];
        if (!empty($res)) {
            foreach ($res as $v) {
                if($v['rp_type'] == 3){
                    $content = '向你扔来一个问答红包';
                }else{
                    $content = $v['rp_content'];
                }
                $item['received_voice']  = $v->received_voice;
                $item['rp_id']           = $v->rp_id;
                $item['create_time']     = $v->create_time;
                $item['rp_content']      = $content;
                $item['duration']        = $v->duration;
                array_push($data,$item);
            }
            return $data;
        }else{
            self::pushError(500,'暂无数据');
        }
    }


    //举报
    public static function report($content,$uid)
    {
        if (empty($content)) {
            self::pushError(500,'请输入举报内容');
        }

        $data['report_content']  = $content;
        $data['create_time']     = time();
        $data['user_id']         = $uid;

       /* if (!empty($phone)) {
            $data['phone'] = $phone;
        }*/

        //return $data;
        $result = ReportModel::insertReport($data);

        if ($result) {
            return ['msg'=>'举报成功'];
        } else {
            self::pushError(500,'服务器忙');
        }
    }


    //举报后台查看
    public static function report_list($page, $search)
    {
        if (empty($page) || $page < 1) {
            $limit = 0;
        } else {
            $limit = ($page - 1) * 10;
        }

        $data = Db::name('report')->where('user_name', 'like', '%' . $search['user_name'] . '%')
            ->where('red_id', 'like', '%' . $search['red_id'] . '%')->order('create_time desc')->limit($limit, 10)->select();

        $data['count']['count'] = Db::name('report')->where('user_name', 'like', '%' . $search['user_name'] . '%')
            ->where('red_id', 'like', '%' . $search['red_id'] . '%')->count();
        foreach ($data as $k) {
            $datas[] = $k;
        }
        return $datas;

    }


    //查看被举报的红包详情
    public static function report_detail($red_id)
    {
        $field = 'red_id,user_id,se_money,se_number,voice,content,receive,create_time,order_sn,type';
        $red = Db::name('send')->where('red_id', $red_id)->field($field)->find();
        if (!$red) {
            self::setError([
                'status_code' => 4055,
                'message' => '请输入正确的红包ID',
            ]);
            return false;
        }
        $red['user'] = Db::name('users')->where('user_id', $red['user_id'])->field('user_icon,user_name')->find();
        if ($red['type'] == 1) {
            $type = '语音红包';
        } elseif ($red['type'] == 2) {
            $type = '口令红包';
        } else {
            $type = '问答红包';
        }
        $red['type'] = $type;
        return $red;
    }


    //后台查看红包二维码
    public static function adQr_list($page)
    {
        if (empty($page) || $page < 1) {
            $limit = 0;
        } else {
            $limit = ($page - 1) * 10;
        }
        $data = Db::name('red_qr')->where(['is_del' => 0])->limit($limit, 10)->select();
        if ($data) {
            return $data;
        } else {
            self::setError([
                'status_code' => 500,
                'message' => '没有数据',
            ]);
            return false;
        }
    }


    //后台添加 红包修改二维码
    public static function adQr_set($data)
    {
        //$data = json_decode($data,true);
        $red = Db::name('send')->where('red_id', $data['red_id'])->find();
        if (empty($red)) {
            $msg = '请输入正确的红包ID';
        }
        $alone = Db::name('red_qr')->where(['red_id' => $data['red_id'], 'is_del' => 0])->find();
        if (!empty($alone)) {
            $msg = '存在相同红包ID';
        }
        if (empty($data['qr_address'])) {
            $msg = '请输入二维码地址';
        }
        $data['create_time'] = time();
        if (isset($msg)) {
            self::setError([
                'status_code' => 4055,
                'message' => $msg,
            ]);
            return false;
        }
        if (empty($data['id'])) {
            unset($data['id']);

            $res = Db::name('red_qr')->insert($data);
        } else {

            $res = Db::name('red_qr')->where(['id' => $data['id'], 'is_del' => 0])->update($data);
        }
        if ($res) {
            return true;
        } else {
            self::setError([
                'status_code' => 500,
                'message' => '服务器忙',
            ]);
            return false;
        }
    }


    public static function adQr_del($id)
    {
        $map = [
            'id' => $id,
            'is_del' => 0
        ];
        $res = Db::name('red_qr')->where($map)->find();
        if (empty($res)) {
            self::setError([
                'status_code' => 4055,
                'message' => '请输入正确的ID',
            ]);
            return false;
        }
        $del = Db::name('red_qr')->where($map)->setInc('is_del');

        if ($del) {
            return true;
        } else {
            self::setError([
                'status_code' => 500,
                'message' => '请输入正确的ID',
            ]);
            return false;
        }

    }

    //增加分享次数
    public static function addShareTimes($privately, $data)
    {
        //判断是否为广告红包
        $is_ex = ExtensionModel::getInfo(['rp_id'=>$data['rp_id']],'id');
        if (empty($is_ex)) {
            return ['message' => '此次分享不增加次数！'];
        }

        //获取openid
        $redis = RedisClient::getHandle(0);
        $openid = $redis->getKey('user_id:'.$privately['user_id']);

        //获取今日0点时间戳
        $today_start = strtotime(date("Y-m-d"), time());

        //解密groupid
        $appid = $privately['appId'];

        $session_key = $redis->getKey('openid:'.$openid);

        $pc = new WXBizDataCrypt($appid, $session_key);
        $errcode = $pc->decryptData($data['encryptedData'], $data['iv'], $newData);

        if ($errcode == 0) {
            //解密成功
            $newData = json_decode($newData,true);
            $groupId = $newData->openGId;

        } else {
            self::pushError($errcode,'校验数据失败！');
        }

        //查询当前时间和群号是否存在
        $share_data = ShareModel::getInfo(['user_id'=>$privately['user_id']],$today_start,'share_id');

        if (empty($share_data)) {
            $save_data = [
                'user_id' => $privately['user_id'],
                'group_id' => $groupId,
                'create_time' => time(),
            ];

            $addLog   = ShareModel::addInfo($save_data);
            $addTimes = UserInfoModel::addShareTimes($privately['user_id']);

            if ($addLog || $addTimes) {
                return [ 'message' => '分享成功！'];
            }else{
                self::pushError(500,'网络超时！');
            }

        } else {
            return ['message' => '分享成功！'];
        }
    }

    /*
  * 检测微信登陆
  * @params array $data 发送的参数
  */
    public static function checkWx($data)
    {
        $validate = validate('app\users\validate\WxLogin');
        if (!$validate->check($data)) {
            self::pushError(4105,$validate->getError());
        }

        $appid = config('wechatapp_id');
        //解密数据，以及验证签名
        $pc = new WXBizDataCrypt($appid, 'session_key');

        $user = new User();
        $where['user_openid'] = $data['openid'];
//        $where['user_unionid'] = $data['unionid'];
        //todo??????
        $res = $user->where($where)->find();


        if (!empty($res)) {
            //更新数据
            $result = $res->getData();
            $payload['requesterID'] = $result['user_id'];
            $payload['identity']    = 'yezhu';
            $payload['exp']         = time() + 604800;
            $result['token']        = JWT::encode($payload, config('jwt-key'));
            $result['identity']     = 'yezhu';
            return $result;
        } else {
            //创建用户id ,业务1
            $user_data['user_id']      = Snowflake::generateParticle(1);
            $user_data['user_openid']  = $data['openid'];
            $user_data['user_unionid'] = $data['unionid'];
            $user_data['user_name']    = $data['nickname'] ?? $user_data['user_id'];
            $user_data['user_icon']    = $data['user_icon'] ?? '';
            $user = new User();
            $res = $user->save($user_data);
            if ($res) {
                $user_info = self::read($user_data['user_id']);
                if ($user_info) {
                    $payload['requesterID'] = $user_info['user_id'];
                    $payload['identity'] = 'yezhu';
                    $payload['exp'] = time() + 604800;
                    $user_info['token'] = JWT::encode($payload, config('jwt-key'));
                    $user_info['identity'] = 'yezhu';
                    return $user_info;
                }
            } else {
                self::pushError(500,'网络请求错误，请稍后重试');
            }
        }
    }
}
