<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/7
 * Time: 16:23
 */

namespace app\redpacket\service;


use app\common\service\PushService;
use Overtrue\Pinyin\Pinyin;
use app\redpacket\service\AudioService;
use think\Request;


class QiniuService extends PushService
{
    public static function speechNew($data,$privately)
    {
        //转码后音频格式和路径
        $wav = 'uploads/audio/'.$privately['user_id'].'.pcm';

        //todo 路径需要根据线上文件路径更换
        $voice = '/www/wwwroot/red.server/public/uploads/'.$data['voice'];

        $command = "/usr/bin/ffmpeg -i {$voice} -acodec pcm_s16le -f s16le -ac 1 -ar 16000 {$wav}";
        $command1 = $command;

        exec( escapeshellcmd($command1), $output,$return_val );

        //删除上传的音频
        unlink($voice);

        //语音识别
        if($return_val){
            self::pushError('500','解析音频失败！');
        }else{
            if(!file_exists($wav)){
                self::pushError('500','网络错误！');
            }
            $speech_res = AudioService::speech(['audioUrl'=> $wav]);
        }

        if($speech_res){
            $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
            $content_pinyin = implode(',',$pinyin->convert($speech_res));

            $speechData['persistentid'] = $data['voice_id'];
            $speechData['voice_url'] = $data['voice_url'];
            $speechData['content'] = $content_pinyin;
            $speechData['create_time']=time();

            return true;
        }else{

            $speechData['persistentid'] = $data['voice_id'];
            $speechData['voice_url'] = $data['voice_url'];
            $speechData['content'] = '';
            $speechData['create_time']=time();

            return true;
        }

        Db::name('speech')->insert($speechData);
        $params['persistentid'] = $data['voice_id'];
        $params['red_id']       = $data['red_id'];
        $params['duration']     = $data['duration'];

        return AudioService::getSpeechRes($params,$uid);

    }

    public static function callback()
    {
        try{

            $q_domain = config('qiniu_bucket_domain');
            //获取回调的body信息
            $callbackData = Request::instance()->post();

            $callbackBody = $callbackData;

            //判断是否转换成功
            $items = $callbackBody['items'];
            $pres = $items[0];
            if($callbackBody['code']==0){
                //成功，请求百度的语音识别接口

                $speech_res = AudioService::speech([
                    'audioUrl'=> $q_domain.'/'.$pres['key']
                ]);

                if($speech_res){
                    //识别成功
                    $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
                    //带声调识别
                    //$content_pinyin = implode(',',$pinyin->convert($speech_res,PINYIN_ASCII));
                    //不带声调识别
                    $content_pinyin = implode(',',$pinyin->convert($speech_res));
                    $speechData['persistentid'] = $callbackBody['id'];
                    $speechData['voice_url'] = $q_domain.'/'.$callbackBody['inputKey'];
                    $speechData['content'] = $content_pinyin;
                    $speechData['create_time']=time();
                }else{
                    $speechData['persistentid'] = $callbackBody['id'];
                    $speechData['voice_url'] = $q_domain.'/'.$callbackBody['inputKey'];
                    $speechData['content'] = '';
                    $speechData['create_time']=time();
                }
            }else{
                $speechData['persistentid'] = $callbackBody['id'];
                $speechData['voice_url'] = $q_domain.'/'.$callbackBody['inputKey'];
                $speechData['content'] = '';
                $speechData['create_time']=time();

            }
            $count= Db::name('speech')->where(['persistentid'=>$callbackData['id']])->count();
            if($count==0){
                Db::name('speech')->insert($speechData);//插入到结果表
            }

            $resp = array('ret' => 'success');

            echo json_encode($resp);
            //连接socker服务器
            //$redis = RedisClient::getHandle(0);
            //$redis->ppush('qiniuId',$callbackData['id']);
            /**原有方式**/
            /*$client = new Client('ws://127.0.0.1:8083');
            $data = json_encode([
                'controller_name'=>'AppController',
                'method_name'=>'getNotice',
                'data'=>$callbackData['id']
            ]);
            $client->send($data);*/
            $url = 'http://localhost:8081/AppController/notice?uid='.$callbackData['id'];
            $client = new GClient();
            $response = $client->get($url);
            $res =$response->getBody()->getContents();
            //echo $client->receive();
            //$client->close();
            /*//回调的签名信息，可以验证该回调是否来自七牛 屏蔽验证
            $header = Request::instance()->header();
            $authorization = $header['authorization'];

            //七牛回调的url，具体可以参考：http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html
            $url = config('qiniu_notify');

            $isQiniuCallback = $auth->verifyCallback($contentType, $authorization, $url, $callbackBody);

            if ($isQiniuCallback) {
                Log::write('七牛回调内容是:',json_encode($callbackBody));
                //$resp = array('ret' => 'success');
            } else {
                //$resp = array('ret' => 'failed');
            }

            //echo json_encode($resp);*/
        }catch (\Exception $e){

            throw new \think\Exception($e->getMessage().' in '.$e->getFile().'行'.$e->getLine(),$e->getCode());
        }

    }

}