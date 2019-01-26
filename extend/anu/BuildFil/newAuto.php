<?php 
namespace anu\BuildFil;
use anu\BuildFil;
use think\Db;
use think\Config;
/**
* 初始化自动识别数据表类
* 该类只需要传入已经设计好的tablename，就可以自动生成自动删除等属性
* 只能支持一个模型
*/
class newAuto
{	
	public static $build;
	public static $module;
	public static $tableInfo;
	public static function run($build)
	{	

		self::$build = $build;
		
		self::$module = array_keys($build)[0];

		self::$tableInfo = Db::getTableInfo(Config::get("database.prefix").self::$build[self::$module]['table_name'][0]);
		// var_dump(self::$tableInfo);die;
		// print_r(self::$module);die;
		//优先自定义，没有走默认
		BuildFil::$namespace = self::namespace();
		
		//优先自定义，没有根据表名首字母大写
        BuildFil::$modelName = self::modelName();
         // var_dump(BuildFil::$modelName);die;
         //优先自定义，没有根据表名首字母大写
        BuildFil::$extends = self::extends();
        // var_dump(BuildFil::$extends);die;
        
        BuildFil::$tablename2 = self::tablename2();
        // var_dump(BuildFil::$tablename2);die;
        BuildFil::$initialize = self::initialize(); 
        // var_dump(BuildFil::$initialize);die;
        //优先自定义，其次框架自定检测并默认
        BuildFil::$pk2 = self::pk2();
        // var_dump(BuildFil::$pk2);die;
        //优先自定义，其次自动搜索是否有is_del字段，如果没有就不写软删除
        BuildFil::$softdel = self::softdel();
        // var_dump(BuildFil::$softdel);die;
        BuildFil::$basequery = self::basequery();
        // var_dump(BuildFil::$basequery);die;
        BuildFil::$create_update_time2 = self::create_update_time2();
        // var_dump(BuildFil::$create_update_time2);die;
        BuildFil::$setAttr = self::setAttr();
        // var_dump(BuildFil::$setAttr);die;
        BuildFil::$getAttr = self::getAttr();
        // var_dump(BuildFil::$getAttr);die;
        BuildFil::$dataComplete = self::dataComplete();
        // var_dump(BuildFil::$dataComplete);die;
        BuildFil::$allowFiled2 = self::allowFiled2();
        // var_dump(BuildFil::$allowFiled2);die;
        BuildFil::$CtlAllowField = self::CtlAllowField();
        // var_dump(BuildFil::$CtlAllowField);die;
        BuildFil::$validate2 = self::validate2();
        BuildFil::$usenamespace = self::usenamespace();
        BuildFil::$error = self::getErrorNum();

		// var_dump(BuildFil::$usenamespace);die;
        // var_dump(BuildFil::$validate2);die;
        // BuildFil::$OOguanlian = self::OOguanlian();
        // BuildFil::$OTguanlian = self::OTguanlian();
        // BuildFil::$belongsTo2 = self::belongsTo2();
	}
	public static function getErrorNum()
	{
		if(empty(self::$build[self::$module]['errorNum'])){
			return BuildFil::$errorNum;
		}else{
			return self::$build[self::$module]['errorNum'];
		}
	}

	public static function usenamespace()
	{	
		//如果!isset或者是true ，那么如果是service直接把model 引入,其他情况返回空字符串
		//如果false 返回"";
		//如果是数组就使用数组
		//如果是字符串就返回错误信息
		if(!isset(self::$build[self::$module]['usenamespace']) || self::$build[self::$module]['usenamespace'] === true){
			if(in_array('service',self::$build[self::$module]['__dir__'])){
				return 'use '.str_replace('service','model',BuildFil::$namespace);
			}else{
				return '';
			}
		}elseif(self::$build[self::$module]['usenamespace'] == false){
			return '';
		}elseif (is_array(self::$build[self::$module]['usenamespace'])) {
			$str = '';
			foreach (self::$build[self::$module]['usenamespace'] as $key => $value) {
$str .= 'use '.$value.';
';
			}
			return $str;
		}elseif(is_string(self::$build[self::$module]['usenamespace]'])){
			return 'usenamespace 不可以是字符串';
		}
	}

	public static function namespace()
	{	
		if(empty(self::$build[self::$module]['namespace'])){
			return BuildFil::$namespace;
		}else{
			return self::$build[self::$module]['namespace'];
		}
	}
	
	public static function modelName()
	{
		if(empty(self::$build[self::$module]['modelName'][0])){
			$table_name = explode('_', self::$build[self::$module]['table_name'][0]);
			$tablename = '';
			foreach ($table_name as $key => $name) {
				$tablename .= ucwords($name);
			}
			return $tablename;
		}else{
			return self::$build[self::$module]['modelName'][0];
		}
	}
	public static function extends()
	{
		if(empty(self::$build[self::$module]['extends'])){
			return BuildFil::$extends;
		}else{
			return self::$build[self::$module]['extends'];
		}
	}
	//不带表名前缀
	public static function tablename2()
	{
		if(empty(self::$build[self::$module]['table_name'][0])){
			echo '表名必填！';die;
		}else{
			return "protected \$name = '".self::$build[self::$module]['table_name'][0]."';";
		}
	}

	public static function initialize()
	{
		if(empty(self::$build[self::$module]['initialize'])){
			return BuildFil::$initialize;
		}else{
			return self::$build[self::$module]['initialize'];
		}
	}
	public static function pk2()
	{
		if(empty(self::$build[self::$module]['pk'])){
			// return = Db::getTableInfo(self::$build[self::$module]['table_name'], 'pk');
			return "protected \$pk = '".self::$tableInfo['pk']."';";
		}else{
			return "protected \$pk = '".self::$build[self::$module]['pk']."';";
		}
	}
	public static function softdel()
	{	//softdel为空，自动检测是否有is_del，有则用，无不用
		if(!isset(self::$build[self::$module]['softdel'])){
			$fields = self::$tableInfo['fields'];
			if(in_array('is_del', $fields)){
				return "use SoftDelete;\n	protected \$deleteTime = 'is_del';";
			}else{
				return '';
			}
		//softdel不为空，全等于false直接为不用软删除
		}elseif(self::$build[self::$module]['softdel'] === false){
			return '';
		//有指定的softdel值，则使之当is_del字段
		}else{
			return "use SoftDelete;\n	protected \$deleteTime = '".self::$build[self::$module]['softdel']."';";
		}
	}
	public static function basequery()
	{	
		//未定义自动检测is_del字段和create_time字段和hidden字段
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
			return '';
		//传自定义的基础查询接口
		}else{
			if(!is_array(self::$build[self::$module]['basequery']) || empty(self::$build[self::$module]['basequery'])){
				echo 'basequery参数错误';die;
			}else{
				$basequery = '';
				foreach (self::$build[self::$module]['basequery'] as $key => $condition) 
				{
						$basequery .= "->".array_keys($condition)[0]."(".array_values($condition)[0].")";
				}
				if(strlen($basequery) > 0){
					return "protected function base(\$query)\n{\n    \$query".$basequery.";\n}";
				}else{
					return '';
				}
			}
		}
	}

	public static function create_update_time2()
	{	
		//未定义自动检测create_time字段和update_time字段
		if(!isset(self::$build[self::$module]['create_update_time'])){
			$create_update_time = '';
			if(in_array('create_time', self::$tableInfo['fields'])){
				$create_update_time .= "	protected \$createTime = 'create_time';\n";
			}
			if(in_array('update_time', self::$tableInfo['fields'])){
				$create_update_time .= "	protected \$updateTime = 'update_time';\n";
			}
			if(strlen($create_update_time) > 0){
				return "	protected \$autoWriteTimestamp = true;\n".$create_update_time;
			}else{
				return '';
			}
		//false 直接不适用新建时间和修改时间的功能
		}elseif(self::$build[self::$module]['create_update_time'] === false){
			return '';
		//传入自定义的字段值
		}else{
			if(!is_array(self::$build[self::$module]['create_update_time']) || empty(self::$build[self::$module]['create_update_time'])){
				echo 'create_update_time参数错误';die;
			}else{
				$create_update_time = '';
				foreach (self::$build[self::$module]['create_update_time'] as $key => $coloum) 
				{
						$create_update_time .= "	protected \$".$key." = \"$coloum\";\n";
				}
				if(strlen($create_update_time) > 0){
					return "	protected \$autoWriteTimestamp = true;\n".$create_update_time;
				}else{
					return '';
				}
			}
		}
	}
	// 修改器
	public static function setAttr()
	{	
		//只有数组形式的字段名才有效，其他均返回空字符串
		if(empty(self::$build[self::$module]['setAttr'])){
			return '';
		}elseif(is_array(self::$build[self::$module]['setAttr'])){
			$setAttr = '';
			foreach (self::$build[self::$module]['setAttr'] as $key => $value){
				$attrArr = explode('_', $value);
				$attStr = '';
				foreach ($attrArr as $key2 => $name) {
					$attStr .= ucwords($name);
				}
				$setAttr .= " 	public function set".$attStr."Attr(\$value)\n    {\n        return \$value;\n    }\n";
			}
			return $setAttr;
		}else{
			echo 'setAttr只能使用数组！';
		}
	}
	// 获取器
	public static function getAttr()
	{	
		//只有数组形式的字段名才有效，其他均返回空字符串
		if(empty(self::$build[self::$module]['getAttr'])){
			return '';
		}elseif(is_array(self::$build[self::$module]['getAttr'])){
			$getAttr = '';
			foreach (self::$build[self::$module]['getAttr'] as $key => $value) {
				$attrArr = explode('_', $value);
				$attStr = '';
				foreach ($attrArr as $key2 => $name) {
					$attStr .= ucwords($name);
				}
				$getAttr .= " 	public function get".$attStr."Attr(\$value)\n    {\n        return \$value;\n    }\n";
			}
			return $getAttr;

		}else{
			echo 'getAttr只能使用数组！';
		}
	}
	// 数据自动完成
	public static function dataComplete()
	{	
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
			$dataComplete = '';
			foreach (self::$build[self::$module]['dataComplete'] as $update => $colAndCondi) {
				$attr = '';
				$condition = '';
				foreach ($colAndCondi as $coloum => $condi) {
					$attr .= "\"".$coloum."\","; 
					$coloum = self::colFormate($coloum);
					$condition .= "\nprotected function set".$coloum."Attr()\n{\n ".$condi."\n}\n";
				}
				$dataComplete .= "\nprotected \$".$update." = [".$attr."];\n".$condition;
			}

			return $dataComplete;
		}else{
			return 'dataComplete只能使用数组';
		}
	}
	
	//model保存save或者update修改时允许的字段 返回一个数组
	public static function allowFiled2()
	{	
		//为空，使用除了主键，create_time和update_time 这三个字段的其他字段
		if(empty(self::$build[self::$module]['allowFiled'])){
			$fields = self::$tableInfo['fields'];
			$banFields = ['create_time','update_time'];
			array_push($banFields,self::$tableInfo['pk']);
			foreach ($fields as $key => $value) {
				if(in_array($value,$banFields)){
					unset($fields[$key]);
				}
			}
			$allowFiled = '';
			$fids = '';
			foreach ($fields as $key => $value) {
				$fids .= "\"".$value."\",";
			}
			$allowFiled .= "->allowField([\n".$fids."\n])";
			$allowFiled2['save'] = $allowFiled;
			$allowFiled2['update'] = $allowFiled;
			return $allowFiled2;
		}elseif(is_array(self::$build[self::$module]['allowFiled'])){
			$arrKeys = array_keys(self::$build[self::$module]['allowFiled']);
			foreach ($arrKeys as $k => $key) {
				if(!in_array($key,['update','save'])){
					echo 'tp只支持allowField:update和save三个属性';
				}
			}
			$allowFiled = [];
			foreach (self::$build[self::$module]['allowFiled'] as $update => $col) {
				$fids = '';
				foreach ($col as $key => $value) {
					$fids .= "\"".$value."\",";
				}
				$allowFiled[$update] = "->allowField([\n".$fids."\n])";
			}
			return $allowFiled;
		}else{
			return 'allowField:只能使用数组!';
		}
	}
	//controller允许接收的字段save,delete,update
	public static function CtlAllowField()
	{	
		//没有定义的话，或者是true 使用默认值
		if(!isset(self::$build[self::$module]['CtlAllowField']) || self::$build[self::$module]['CtlAllowField'] === true){
			$fields = self::$tableInfo['fields'];
			$banFields = ['create_time','update_time','is_del'];
			array_push($banFields,self::$tableInfo['pk']);
			foreach ($fields as $key => $value) {
				if(in_array($value,$banFields)){
					unset($fields[$key]);
				}
			}
			$allowFiled = '';
			$fids = '';
			foreach ($fields as $key => $value) {
				$fids .= "\"".$value."\",";
			}
			$allowFiled2['save'] = "->save(\$request->only([\n".$fids."\n],'post'))";
			$allowFiled2['update'] = "->save(\$request->only([\n".$fids."\n,".self::$tableInfo['pk']."],'put'),\$id)";
			$allowFiled2['delete'] = "->delete(\$request->only([\n".self::$tableInfo['pk']."\n],'delete'))";
			return $allowFiled2;
		//false关闭该功能，就会使用所有的字段(不可能完全关闭该功能)
		}elseif(self::$build[self::$module]['CtlAllowField'] === false){
			$fields = self::$tableInfo['fields'];
			$fids = '';
			foreach ($fields as $key => $value) {
				$fids .= "\"".$value."\",";
			}
			$allowFiled2['save'] = "->save(\$request->only([\n".$fids."\n],'post'))";
			$allowFiled2['update'] = "->save(\$request->only([\n".$fids."\n],'put'),\$id)";
			$allowFiled2['delete'] = "->delete(\$request->only([\n".self::$tableInfo['pk']."\n],'delete'))";
			// print_r($allowFiled2);die;
			return $allowFiled2;
		//是数组的话可能是正确的数据
		}elseif(is_array(self::$build[self::$module]['CtlAllowField'])){
			//只能是save delete update
			$arrKeys = array_keys(self::$build[self::$module]['CtlAllowField']);
			foreach ($arrKeys as $k => $key){
				if(!in_array($key,['save','delete','update'])){
					echo 'tp-CtlAllowField只支持save delete update三个属性';
				}
			}
			$allowFiled = [];
			foreach (self::$build[self::$module]['CtlAllowField'] as $update => $col) {
				$fids = '';
				foreach ($col as $key => $value) {
					$fids .= "\"".$value."\",";
				}
				if($update == 'delete'){
					$allowFiled[$update] = "->delete(\$request->only([\n".$fids."\n],'delete'))";
				}elseif($update =='save'){
					$allowFiled[$update] = "->save(\$request->only([\n".$fids."\n],'post'))";
				}elseif($update == 'update'){
					$allowFiled[$update] = "->save(\$request->only([\n".$fids."\n],'put'),\$id)";
				}
				
			}
			return $allowFiled;
		//其他情况的话，肯定是字符串或者值了
		}else{
			return 'CtlAllowField:只能使用数组!';
		}
	}
	//验证器
	public static function validate2()
	{	$mysqlType = self::getMysqlColoumType();
		//不定义或者为true为默认
		if(!isset(self::$build[self::$module]['validate']) || self::$build[self::$module]['validate'] === true){
			$type = self::$tableInfo['type'];
			$type = self::analyzeColoumTypes($type);
			$rule = '';
			$scene = '';
			foreach ($type as $key => $value) {
				if($key == self::$tableInfo['pk'])continue;
				$condition = '';
				$condiArr = [];
				$str = $value[0];
				$num = $value[1];
				if(in_array($str,$mysqlType)){
					$index = array_search(strtolower($str), $mysqlType);
					if($index !== false && $index <= 11){
						$condiArr[] = 'number';
					}
					if(isset($num))$condiArr[] = 'max:'.$num;
				}else{
					return $key.'错误的mysql字段类型';
				}
				$condition = implode('|', $condiArr);
				$rule .=  '"'.$key.'" => "'.$condition.'",
				';
				$scene .=  '"'.$key.'" => "require|'.$condition.'",
				';
			}
			$result = "protected \$rule = [
			".$rule."
			]; 
			\n protected \$scene = [
				'save' => [
				".$scene."
				],
				'update' => [
				".$scene."
				],
				];";
			return $result;
		//为false 可以不使用验证器
		}elseif(self::$build[self::$module]['validate'] === false){
			return '';
		//是数组的话可能是正确的数据，是的话完全用自定义的属性,scene save update 都不能为空
		}elseif(is_array(self::$build[self::$module]['validate'])){
			$arrKeys = array_keys(self::$build[self::$module]['validate']);
			foreach ($arrKeys as $k => $key){
				if(!in_array($key,['rule','scene'])){
					echo 'validate只支持rule scene 两个属性';
				}
			}
			$validateRule = self::$build[self::$module]['validate']['rule'];
			$rule = '';
			foreach ($validateRule as $key => $value) {
				$rule .= '"'.$key.'" => "'.$value.'",
				';
			}

			$validateUpdate = self::$build[self::$module]['validate']['scene']['update'];
			$update = '';
			foreach ($validateUpdate as $key => $value) {
				$update .= '"'.$key.'" => "'.$value.'",
				';
			}

			$validateSave = self::$build[self::$module]['validate']['scene']['save'];
			$save = '';
			foreach ($validateSave as $key => $value) {
				$save .= '"'.$key.'" => "'.$value.'",
				';
			}

			$result = "protected \$rule = [
			".$rule."
			]; 
			\n protected \$scene = [
				'save' => [
				".$save."
				],
				'update' => [
				".$update."
				],
				];";
			return $result;
		//其他情况的话，肯定是字符串，返回错误	
		}else{
			return 'validate:只能使用数组！';
		}
	}
	public static function getMysqlColoumType()
	{
		return $mysqlType = [

			'tinyint',//0
			'smallint',
			'mediumint',
			'int',
			'bigint',
			'dicimal',
			'float',
			'double',
			'real',
			'bit',
			'boolean',
			'serial',//11

			"date",//12
		    "datetime",
		    "timestamp",
		    "time",
		    "year",//16

	        "char",//17
	        "varchar",
	        "tinytext",
	        "text",
	        "mediumtext",
	        "longtext",
	        "binary",
	        "varbinary",
	        "tinyblob",
	        "mediumblob",
	        "blob",
	        "longblob",
	        "enum",
	        "set",//30
		];
	}
	public static function analyzeColoumTypes($types)
	{	
		$result = [];
		foreach ($types as $key => $value) {
			$type = self::getColoumTypeStr($value);
			$str = array_keys($type)[0];
			$num = array_values($type)[0];
			$result[$key][0] = $str;
			$result[$key][1] = $num;
		}
		return $result;
	}

	public static function getColoumTypeStr($type)
	{	
		$result = [];
		preg_match("/^(.*?)(\((.*?)\))*$/",$type,$str);
		$str1 = $str[1];
		$num = isset($str[3])?$str[3]:null;
		$result[$str1]=$num;
		return $result;
	}

	//一对一关联
	public static function OOguanlian()
	{
		if(empty(self::$build[self::$module]['OOguanlian'])){
			return '';
		}else{

		}
	}
	//一对多关联
	public static function OTguanlian()
	{
		if(empty(self::$build[self::$module]['OTguanlian'])){
			return '';
		}else{

		}
	}
	//反向关联
	public static function belongsTo2()
	{
		if(empty(self::$build[self::$module]['belongsTo2'])){
			return '';
		}else{

		}
	}

	/**
	 * 首字母转大写
	 * @param  string $coloum 传入以"_"分隔的字符串
	 * @return string         返回首字母大写
	 */
	private static function  colFormate($coloum)
	{	
		$attrArr = explode('_', $coloum);
		$attStr = '';
		foreach ($attrArr as $key2 => $name) {
			$attStr .= ucwords($name);
		}
		return $attStr;
	}
}