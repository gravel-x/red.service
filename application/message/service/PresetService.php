<?php
/**
 * Created by PhpStorm.
 * User: gravel
 * Date: 2018/4/13
 * Time: 13:41
 */
namespace app\message\service;
use app\common\model\RedpacketModel;
use app\common\service\PushService;
use app\message\model\NotesModel;
use app\message\model\PresetModel;
use app\message\model\TemplateModel;
use app\users\service\UserService;
use redis\RedisClient;

class PresetService extends PushService
{
    //前端查询口令
    public static function getPreset()
    {
        $redis  = RedisClient::getHandle(0);

        $redis_array = $redis->getKey('preset');

        if (empty($redis_array)) {

            $res = PresetModel::getPersetInfo('');

            if ($res) {
                $redis->setKey('preset',json_encode($res));
            }else{
                self::pushError(500,'网络错误！');
            }
        }else{
            $res = json_decode($redis_array,true);
        }

        return $res;
    }

    //显示弹幕
    public static function barrage()
    {

        $speed = Config('speed');

        $redis  = RedisClient::getHandle(0);
        $redis_array = $redis->getKey('notes');

        if (empty($redis_array)) {
            $res = NotesModel::getNotesInfo(['is_show'=>1]);
            if ($res) {
                $redis->setKey('notes',json_encode($res));
            }else{
                self::pushError(500,'网络错误！');
            }
        }else{
            $res = json_decode($redis_array,true);
        }

        if(!empty($res)){
            $res['speed'] = $speed;
            return $res;
        }else{
            self::pushError(500,'暂无数据');
        }

    }

    //修改弹幕
    public static function update_barrage($info)
    {
        $data['notes_content'] = $info['content'];
        $data['notes_color']   = $info['color'];
        $data['notes_speed']   = $info['speed'];

        $res = NotesModel::updateNotes(['notes_id'=>$info['id']],$data);
        if($res){
            $data['msg'] = '修改成功';
            return $data;
        }else{
            self::pushError(500, '服务器忙！');
        }

    }

    //弹幕开关
    public static function is_barrage($info)
    {
        if (!is_numeric($info['id']) && !is_numeric($info['is_show'])) {
            self::pushError(500, '参数有误！');
        }
        if($info['is_show'] != 0  &&  $info['is_show'] != 1){
            self::pushError(500, '参数有误！');
        }

        $res = NotesModel::setFieldNotes(['notes_id'=>$info['id']],['is_show'=>$info['is_show']]);

        if ($res) {
            if($info['is_show'] == 0){
                return ['msg'=>'弹幕已开启'];
            }else{
                return ['msg'=>'弹幕已关闭'];
            }
        }else{
            self::pushError(500, '服务器忙！');
        }
    }


    //比率
    public static function getProportion()
    {
        $data_result = config('proportion');

        if(!empty($data_result)){
            return ['message' => $data_result['service']];
        }else{
            self::pushError(500,'暂无数据');
        }
    }



    //模板消息
    public static function template($info,$uid)
    {
        $ACCESS_TOKEN = UserService::ac_token();
        $res = RedpacketModel::findData(['rp_id'=>$info['rp_id']],'*');

        $redis  = RedisClient::getHandle(0);
        $openid = $redis->getKey('user_id:'.$uid);
        //后续逻辑发布
        $template_id = TemplateModel::getTemplate(['rp_type'=>$res['rp_type']],'id');

        if($res['rp_type'] == 2){
            $data = array(
                'touser' => $openid,
                'template_id' => $template_id,
                'form_id'=>$info['form_id'],
                'page'=>'pages/recive/recive?rp_id='.$info['rp_id'],
                'data'=>[
                    'keyword1'=>['value'=>$res['rp_content'],'color'=>'#5C81FF'],
                    'keyword2'=>['value'=>date('m-d H:i'),'color'=>'#5C81FF'],
                    'keyword3'=>['value'=>'您的口令红包已经创建成功，赶快点击分享给小伙伴','color'=>'#5C81FF']
                ]
            );
        }else{
            $data = array(
                'touser'      => $openid,
                'template_id' => $template_id,
                'form_id'     => $info['form_id'],
                'page'        => 'pages/recive/recive?rp_id='.$info['rp_id'],
                'data'        => ['keyword1'=>['value'=>'真心寄语','color'=>'#5C81FF'], 'keyword2'=>['value'=>date('m-d H:i'),'color'=>'#5C81FF']]
            );
        }

        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $ACCESS_TOKEN['access_token'];
        $res = self::Http($url,$data,'json');

        $res = json_decode($res, true);

        if ($res['errcode'] == 0 && $res['errmsg'] == "ok"){
            return ['message'=>'发送成功'];
        }else{
            self::pushError($res['errcode'],$res['errmsg']);
        }
    }

    //CURL请求
    public  static function Http($url,$data,$type="http"){
        $curl = curl_init();
        if ($type == "json"){
            $headers = array("Content-type: application/json;charset=UTF-8");
            $data=json_encode($data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}
