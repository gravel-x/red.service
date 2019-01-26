<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/28
 * Time: 15:54
 */
namespace redis;

use think\Exception;
use think\Log;
class RedisClient
{
    private $host = '127.0.0.1';//sever地址
    private $port = 6379;//server端口
    private $pass='weiju_redis';
    private $redis;
    private static $handle=null;
    public $db;
    private function __construct($db)
    {
        try{
            $this->redis  = new \Redis();
            $this->redis->connect($this->host,$this->port);
            $this->redis->auth($this->pass);
            $this->redis->select($db);
            $this->db = $db;
        }catch (\Exception $e){
            throw new Exception($e->getMessage(),$e->getCode());
        }
        /*if($this->redis->connect($this->host,$this->port)){

        }else{
            echo '失败';
        }*/

    }

    public function ppush($channel,$v)
    {
        $this->redis->publish($channel,$v);
    }
    public static function getHandle($db=2)
    {
        if(self::$handle===null)
        {
            self::$handle = new self($db);

            //$this->handle = new self();
        }
        return self::$handle;
    }

    /**
     *　设置key值
     * @access public
     * @param string $name key名
     * @param string $value  key值
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function setKey($name,$value,$expire=0)
    {
        try{
            if($expire&& is_int($expire)){
                $res = $this->redis->setex($name,$expire,$value);
            }else{
                $res = $this->redis->set($name,$value);
            }
            return $res;
        }catch (Exceptionb $e){
            throw new Exception($e->getMessage(),$e->getCode());
        }

    }
    public function keyIncr($key)
    {
        return $this->redis->incr($key);
    }
    public function keyExists($key)
    {
        return $this->redis->exists($key);
    }
    /**
     * 获取key值
     * @access public
     * @param string $value key名
     * @return mix
     */
    public function getKey($name)
    {
        $res = $this->redis->get($name);
        return $res;
    }

    /**
     * 清空key
     * @param $name
     * @return int
     */
    public function clearKey($name)
    {
        $res = $this->redis->set($name,null);
        return $res;
    }
    /**
     * set集合里面添加元素
     * @access public
     * @param string $name 集合名
     * @return boolean
     */
    public function add_set($name,$value)
    {
        if($value)
        {
            $res = $this->redis->sAdd($name,$value);
            return $res;
        }
    }

    public function getInfo()
    {
        $res = $this->redis->info();
        return $res;
    }
    /**
     * 判断集合中是否含有元素
     * @access public
     * @param string $name 集合名
     * @return boolean
     */
    public function in_set($name, $value)
    {
        try{
            $res = $this->redis->sIsMember($name, $value);
            return $res;
        }catch (\Exception $e){
            throw new \think\Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * 删除集合中的元素
     */
    public function del_set($name,$value)
    {
        $res = $this->redis->sRem($name, $value);
        return $res;

    }

    /**
     * @param $name
     * @return int
     */
    public function getSetCount($name)
    {
        $res = $this->redis->scard($name);
        return $res;
    }
    /**
     * 设置hash表key中的field字段值为value
     * @param $key
     * @param $field
     * @param $value
     * @param int $expire
     * @return int
     */
    public function hashset($key,$field,$value,$expire=0)
    {
        $res = $this->redis->hSet($key,$field,$value);
        if($expire){
            $this->redis->expire($key,$expire);
        }
        return $res;
    }

    /**
     * 设置key过期时间
     */
    public function keyexpire($key,$expire)
    {
        $res = $this->redis->expire($key,$expire);
        return $res;
    }
    public function hashexists($key)
    {
        $res = $this->redis->hLen($key);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取hash表key中field字段的值
     * @param $key
     * @param $field
     * @return string
     */
    public function hashget($key,$field)
    {
        $res = $this->redis->hGet($key,$field);
        return $res;
    }
    /*
     * 获取hahs表key中指定field字段的值
     * @param string $key
     * @param array $fields
     *
     */
    public function hashMget($key,$fields){
        $res = $this->redis->hMGet($key,$fields);
        return $res;
    }
    //HINCRBY为哈希表中的字段值加上指定增量值。
    public function hashIncrby($key,$field,$number)
    {
        $res = $this->redis->hIncrBy($key,$field,$number);
        return $res;
    }

    /**
     *增加一个元素
     */
    public function zsetAdd($key,$int,$item)
    {
        $res = $this->redis->zAdd($key,$int,$item);
        return $res;
    }
    public function zsetIncrby($key,$increment,$item)
    {
        $res = $this->redis->zIncrBy($key,$increment,$item);
        return $res;
    }
    public function zsetScore($key,$item)
    {
        $res = $this->redis->zScore($key,$item);
        return $res;
    }
    /*
     * 批量向sort set中添加元素
     * $key str: sort set的key
     * $item array:待添加元素的集合，每一项为array('val' => score)
     * */
    public function zAddArray($key, $item){
        if (!is_array($item)){
            return false;
        }

        $p[] = $key;
        foreach ($item as $k => $v){
            $p[] = $v;
            $p[] = $k;
        }

        $res = call_user_func_array(array($this->redis, 'zAdd'), $p);

        return $res;
    }

    /**
     * 有序集合按分数从大到小排序
     * @param $key
     * @param int $start
     * @param int $end
     * @return array
     */
    public function declineZset($key,$start=0,$end=-1,$withscores=false)
    {
        $res = $this->redis->zRevRange($key,$start,$end,$withscores);
        return $res;
    }
    /**
     * 获取集合中指定成员的排名
     */
    public function getrankBymember($key,$item)
    {
        $res = $this->redis->zRevRank($key,$item);
        return $res+1;
    }

    /**
     * 合并多个有序集合到新集合中
     * @param $nkey
     * @param $okeys
     * @return int
     */
    public function unionZset($nkey,$okeys)
    {
        $res = $this->redis->zUnion($nkey,$okeys);
        return $res;
    }

    public function getZetCount($key)
    {
        $res = $this->redis->zCard($key);
        return $res;
    }
    /**

     * 加入队列
     */
    public function pushList($key,$value)
    {
        $res = $this->redis->rPush($key,$value);
        return $res;
    }

    /**
     * 出队列
     */
    public function popList($key)
    {
        $res = $this->redis->rPop($key);
        return $res;
    }

    /**
     * 根据$key 查询队列
     * @param $key
     * @return array
     */
    public function getValue($key)
    {
        $res = $this->redis->lRange($key,0,-1);
        return $res;
    }

    /**
     * 根据$key 返回key的剩余时间
     * @param $key
     * @return int
     */
    public function getValueTime($key)
    {
        $res = $this->redis->ttl($key);
        return $res;
    }

    public function setKeyTime($key,$time)
    {
        $res = $this->redis->expireAt($key,$time);
        return $res;
    }

    public function delByKey($key)
    {
        $res = $this->redis->del($key);
        return $res;
    }

    /**
     * 直接设置一个key=>val()->array() 的缓存
     */
    public function setArray($key,$array)
    {
        $res = $this->redis->sAddArray($key,$array);
        return $res;
    }
}