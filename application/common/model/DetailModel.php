<?php
/**
 * User: Administrator
 * Date: 2018/4/10
 * Time: 15:33
 */
namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;
class DetailModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'is_del';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;

    // 定义全局的查询范围 未删除
    protected function base($query)
    {
        $query->where('is_del','=',0);
    }
}