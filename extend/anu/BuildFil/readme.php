<?php 
//	生成model
 	//1:namespace
    public static $namespace = 'app\common\model';
    //2:继承\app\common\model\Base
    public static $extends = '\app\common\model\Base';
    //模型名
    public static $modelName;
    //初始化
    public static $initialize = "protected function initialize()\n{\nparent::initialize();\n}";
    //3:配置表名
    public static $tablename2 = "protected \$name = 'table_name';";
    //4:配置主键
    public static $pk2 = "protected \$pk = 'pk';";

    //5:软删除
    public static $softdel = "use SoftDelete;\nprotected \$deleteTime = 'is_del';";
    //6:基础查询
    public static $basequery = "protected function base(\$query)\n{\n\$query->where('is_del','=',0)->order('create_time','desc');\n}";
    //7:新增时间和修改时间
    public static $create_update_time2 = "protected \$autoWriteTimestamp = true;\nprotected \$createTime = 'create_time';\nprotected \$updateTime = 'update_time';";
    //8:添加修改器
    public static $setAttr = "public function setNameAttr(\$value,\$data)\n{\nreturn serialize(\$data);\n}";
    //9:获取器
    public static $getAttr = "public function getStatusAttr(\$value)\n{\n\$status = [-1=>'删除',0=>'禁用',1=>'正常',2=>'待审核'];\nreturn \$status[\$value];\n}";
    //10:数据完成
    public static $dataComplete = "protected \$auto = [];\nprotected \$insert = ['reg_ip','status' => 1];\nprotected \$update = ['login_ip'];\nprotected function setIpAttr()\n{\nreturn request()->ip();\n}";
    //11:过滤保存字段(allowFiled)
    public static $allowFiled2 =["save" => "->allowField(['account','password','nickname'])","update" => "->allowField(['account','password','nickname'])"];
    //16:控制器过滤字段
    public static $CtlAllowField = ["save" => "","delete" => "","update" => ""];
    //12:验证规则验证器（validate）
    public static $validate2 = "protected \$rule = [];\nprotected \$message = [];";
    //13:定义一对一关联
    public static $OOguanlian = "public function profile()\n{\nreturn \$this->hasOne('profile')->field('id,name,email');\n}";
    //14:定义一对多关联
    public static $OTguanlian = "public function comments()\n{\nreturn \$this->hasMany('Comment');\n}";
    //15:定义相对关联
    



if(!isset(self::$build[self::$module]['basequery'])){
	$fields = self::$tableInfo['fields'];
	$basequery = '';
	if(in_array('is_del', self::$tableInfo['fields'])){
		$basequery .= "->where(\"is_del\",\"=\",0)"; 
	}
	if(in_array('hidden', self::$tableInfo['fields'])){
		$basequery .= "->where(\"hidden\",\"=\",0)"; 
	}
	if(in_array('create_time', self::$tableInfo['fields'])){
		$basequery .= "->order(\"create_time\",\"desc\")"; 
	}
	if(strlen($basequery) > 0){
		return "protected function base(\$query)\n{\n 	\$query".$basequery.";\n}";
	}else{
		return '';
	}
//false 直接不用基础查询
}elseif(self::$build[self::$module]['basequery'] === false){



//空没有,需要指定auto、insert和update 属性，每个属性下的字段，和每个字段的完成条件
if(empty(self::$build[self::$module]['dataComplete'])){
	return '';
}elseif(is_array(self::$build[self::$module]['dataComplete'])){
	$arrKeys = array_keys(self::$build[self::$module]['dataComplete']);
	foreach ($arrKeys as $k => $key) {
		if(!in_array($key,['update','insert','auto'])){
			echo 'tp只支持dataComplete:auto、insert和update三个属性';
		}
	}


validate => [
	[
		'number',
		'max:X',
		'require',
		'strtotime',
		'chsDash',
		'activeUrl'
	]
]