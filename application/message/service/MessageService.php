<?php
/**
 * User: Administrator
 * Date: 2018/4/11
 * Time: 10:05
 */

namespace app\message\service;

use app\common\model\UserInfoModel;
use app\common\service\PushService;
use app\users\service\UserService;
use think\Db;
use think\Log;
use redis\RedisClient;

class MessageService extends PushService
{
    //是否推送
    public static function message($uid, $status)
    {
        $validate = validate('app\message\validate\Message');
        if (!$validate->check($status)) {
            self::pushError(4105, $validate->getError());
        }
        if ($status['status'] == 1) {
            $res = UserInfoModel::changData(['user_id' => $uid], ['is_push' => 1]);
            if ($res) {
                $info['message'] = '设置成功1';
                return $info;
            } else {
                self::pushError(500, '设置失败1');
            }
        } elseif ($status['status'] == 0) {
            $res = UserInfoModel::changData(['user_id' => $uid], ['is_push' => 0]);
            if ($res) {
                $info['message'] = '设置成功0';
                return $info;
            } else {
                self::pushError(500, '设置失败0');
            }
        } else {
            self::pushError(500, '参数有误');
        }
    }

    public static function words($info)
    {
        //搜索名称
        if (!empty($info['title'])) {
            $validate = validate('app\message\validate\Word');
            if (!$validate->check($info)) {
                self::pushError(4105, $validate->getError());
            }
            $res = Db::name('words')->where('title', 'like', '%' . $info['title'] . '%')->select();
            foreach ($res as $k => $v) {
                if ($v['pid'] == 0) {
                    $res[$k]['content'] = Db::name('words')->where('pid', $v['id'])->select();
                }
            }
            if (!empty($res)) {
                $result['total'] = Db::name('words')->where('title', 'like', '%' . $info['title'] . '%')->count();
                $result['res'] = $res;
                return $result;
            } else {
                self::pushError(500, '服务器忙！');
            }
        }

        $res = Db::name('words')->where('pid', 0)->select();

        foreach ($res as $k => $v) {
            $res[$k]['content'] = Db::name('words')->where('pid', $v['id'])->select();
        }
        if (!empty($res)) {
            $result['total'] = Db::name('words')->count();
            $result['res'] = $res;
            return $result;
        } else {
            self::pushError(500, '服务器忙！');
        }
    }

    //添加口令
    public static function postWord($info)
    {
        $validate = validate('app\message\validate\Word');
        if (!$validate->check($info)) {
            self::pushError(4105, $validate->getError());
        }
        $res = Db::name('words')->where('title', $info['title'])->value('id');
        if ($res) {
            $content = ['title' => $info['content'], 'pid' => $res, 'create_time' => time()];
            $res = Db::name('words')->insert($content);
            if ($res) {
                return ['msg' => '添加成功'];
            } else {
                self::pushError(500, '添加失败');
            }
        } else {
            $title = ['title' => $info['title'], 'pid' => 0, 'create_time' => time()];
            $id = Db::name('words')->insertGetId($title);
            $content = ['title' => $info['content'], 'pid' => $id, 'create_time' => time()];
            $res = Db::name('words')->insert($content);
            if ($res) {
                return ['msg' => '添加成功'];
            } else {
                self::pushError(500, '添加失败');
            }
        }
    }

    //删除或修改口令
    public static function del_word($info)
    {
        if (!is_numeric($info['id'])) {
            self::pushError(500, 'id参数非法');
        }

        if ($info['type'] != 0 && $info['type'] != 1) {
            self::pushError(500, 'type参数非法');
        }

        if ($info['type'] == 0) {
            $id = Db::name('words')->where('pid', $info['id'])->find();
            if ($id) {
                self::pushError(500, '请先删除下面的子类');
            } else {
                $res = Db::name('words')->where('id', $info['id'])->delete();
                if ($res) {
                    return ['msg' => '删除成功'];
                } else {
                    self::pushError(500, '服务器忙!');
                }
            }
        } else {
            $validate = validate('app\message\validate\Word');
            if (!$validate->check($info)) {
                self::pushError(4105, $validate->getError());
            }

            $res = Db::name('words')->where('id', $info['id'])->setField('title', $info['content']);
            if ($res) {
                return ['msg' => '修改成功'];
            } else {
                self::pushError(500, '服务器忙!');
            }
        }
    }


}
