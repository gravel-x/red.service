<?php
/**
 * User: Administrator
 * Date: 2018/4/10
 * Time: 17:45
 */
namespace app\message\controller;
use app\common\controller\Middle;
use app\message\service\MessageService;
use think\Request;

class Message extends Middle
{
    //是否推送
    public function is_push()
    {
        $uid = $this->privately['user_id'];
        $status = input('get.');
        $res = MessageService::message($uid,$status);
        $this->pushSuccess($res);
    }

    //对象转数组
    public static function object_array($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)self::object_array($v);
            }
        }
        return $obj;
    }

    //后台口令展示
    public function words()
    {
        $info = Request::instance()->post();
        $res = MessageService::words($info);
         $this->pushSuccess($res);
    }

    //口令添加
    public function add_word()
    {
        $info = Request::instance()->post();
        $res = MessageService::postWord($info);
        $this->pushSuccess($res);
    }

    //口令删除或修改
    public function del_word()
    {
        $info = Request::instance()->post();
        $res = MessageService::del_word($info);
        $this->pushSuccess($res);
    }

}