<?php

namespace anu;
use anu\BuildFil\newAuto;

class BuildFil
{   
    //1:namespace
    public static $namespace = 'app\common\model';

    public static $usenamespace = '';
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
    // public static $OOguanlian = "public function profile()\n{\nreturn \$this->hasOne('profile')->field('id,name,email');\n}";
    //14:定义一对多关联
    // public static $OTguanlian = "public function comments()\n{\nreturn \$this->hasMany('Comment');\n}";
    // 15:定义相对关联
    // public static $belongsTo2 = "public function article()\n{\nreturn \$this->belongsTo('article');\n}";
    public static $error = 40000;

    public static function init($build)
    {   
        newAuto::run($build);
    }
    /**
     * 根据传入的build资料创建目录和文件
     * @access protected
     * @param  array  $build build列表
     * @param  string $namespace 应用类库命名空间
     * @param  bool   $suffix 类库后缀
     * @return void
     */
    public static function run($build = [], $namespace = 'app', $suffix = false)
    {   
        self::init($build);
        // ===========================
        // 
        // 锁定
        // $lockfile = APP_PATH . 'build.lock';
        // if (is_writable($lockfile)) {
        //     return;
        // } elseif (!touch($lockfile)) {
        //     throw new Exception('应用目录[' . APP_PATH . ']不可写，目录无法自动生成！<BR>请手动生成项目目录~', 10006);
        // }
        // 
        // ============================
        foreach ($build as $module => $list) {
            if ('__dir__' == $module) {
                // 创建目录列表
                self::buildDir($list);
            } elseif ('__file__' == $module) {
                // 创建文件列表
                self::buildFile($list);
            } else {
                // 创建模块
                self::module($module, $list, $namespace, $suffix);
            }
        }
        // 解除锁定
        // unlink($lockfile);
    }

    /**
     * 创建目录
     * @access protected
     * @param  array $list 目录列表
     * @return void
     */
    protected static function buildDir($list)
    {
        foreach ($list as $dir) {
            if (!is_dir(APP_PATH . $dir)) {
                // 创建目录
                mkdir(APP_PATH . $dir, 0755, true);
            }
        }
    }

    /**
     * 创建文件
     * @access protected
     * @param  array $list 文件列表
     * @return void
     */
    protected static function buildFile($list)
    {
        foreach ($list as $file) {
            if (!is_dir(APP_PATH . dirname($file))) {
                // 创建目录
                mkdir(APP_PATH . dirname($file), 0755, true);
            }
            if (!is_file(APP_PATH . $file)) {
                file_put_contents(APP_PATH . $file, 'php' == pathinfo($file, PATHINFO_EXTENSION) ? "<?php\n" : '');
            }
        }
    }

    /**
     * 创建模块
     * @access public
     * @param  string $module 模块名
     * @param  array  $list build列表
     * @param  string $namespace 应用类库命名空间
     * @param  bool   $suffix 类库后缀
     * @return void
     */
    public static function module($module = '', $list = [], $namespace = 'app', $suffix = false)
    {
        $module = $module ? $module : '';
        if (!is_dir(APP_PATH . $module)) {
            // 创建模块目录
            mkdir(APP_PATH . $module);
        }
        if (basename(RUNTIME_PATH) != $module) {
            // 创建配置文件和公共文件
            self::buildCommon($module);
            // 创建模块的默认页面
            self::buildHello($module, $namespace, $suffix);
        }
        if (empty($list)) {
            // 创建默认的模块目录和文件
            $list = [
                '__file__' => ['wxpay.php', 'common.php'],
                '__dir__'  => ['controller', 'model', 'view'],
            ];
        }
        // 创建子目录和文件
        // var_dump($list);die;
        foreach ($list as $path => $file) {
            $modulePath = APP_PATH . $module . DS;//string(52) "D:\phpStudy\WWW\yjlmv1\public/../application/common\"
            // var_dump($path);die;//string(9) "namespace"
            if ('__dir__' == $path) {
                // 生成子目录
                foreach ($file as $dir) {
                    if (!is_dir($modulePath . $dir)) {
                        // 创建目录
                        $a = $modulePath.$dir;
                        var_dump($a);die;
                        mkdir($modulePath . $dir, 0755, true);
                    }
                }
            } elseif ('__file__' == $path) {
                // 生成（空白）文件
                foreach ($file as $name) {
                    if (!is_file($modulePath . $name)) {
                        file_put_contents($modulePath . $name, 'php' == pathinfo($name, PATHINFO_EXTENSION) ? "<?php\n" : '');
                    }
                }
            } else {
                // 生成相关MVC文件
                // var_dump($file);die;
                foreach ($file as $val){

                    $val      = trim($val);
                    $filename = $modulePath . $path . DS . $val . ($suffix ? ucfirst($path) : '') . EXT;
                    $space    = $namespace . '\\' . ($module ? $module . '\\' : '') . $path;
                    $class    = $val . ($suffix ? ucfirst($path) : '');
                    // var_dump($path);die;
                    switch ($path) {
                        case 'service':
                            // $content = "<?php\nnamespace {$space};\n\nclass {$class}\n{\n\n}";
                            $content = self::getServicePage();
                            break;
                        case 'controller': // 控制器
                            $content = self::getControllerPage();
                            break;
                        case 'model': // 模型
                            $content = self::getModelPage();
                            break;
                        case 'view': // 视图
                            $filename = $modulePath . $path . DS . $val . '.html';
                            if (!is_dir(dirname($filename))) {
                                // 创建目录
                                mkdir(dirname($filename), 0755, true);
                            }
                            $content = '';
                            break;
                        default:
                            // 其他文件
                            $content = "<?php\nnamespace {$space};\n\nclass {$class}\n{\n\n}";
                    }
                    if (!is_file($filename)) {
                        file_put_contents($filename, $content);
                    }
                }
            }
        }
    }

    /**
     * 创建模块的欢迎页面
     * @access public
     * @param  string $module 模块名
     * @param  string $namespace 应用类库命名空间
     * @param  bool   $suffix 类库后缀
     * @return void
     */
    protected static function buildHello($module, $namespace, $suffix = false)
    {
        $filename = APP_PATH . ($module ? $module . DS : '') . 'controller' . DS . 'Village' . ($suffix ? 'Controller' : '') . EXT;
        if (!is_file($filename)) {
            $content = file_get_contents(THINK_PATH . 'tpl' . DS . 'default_index.tpl');
            $content = str_replace(['{$app}', '{$module}', '{layer}', '{$suffix}'], [$namespace, $module ? $module . '\\' : '', 'controller', $suffix ? 'Controller' : ''], $content);
            if (!is_dir(dirname($filename))) {
                mkdir(dirname($filename), 0755, true);
            }
            file_put_contents($filename, $content);
        }
    }

    /**
     * 创建模块的公共文件
     * @access public
     * @param  string $module 模块名
     * @return void
     */
    protected static function buildCommon($module)
    {
        $filename = CONF_PATH . ($module ? $module . DS : '') . 'wxpay.php';
        if (!is_file($filename)) {
            file_put_contents($filename, "<?php\n//配置文件\nreturn [\n\n];");
        }
        $filename = APP_PATH . ($module ? $module . DS : '') . 'common.php';
        if (!is_file($filename)) {
            file_put_contents($filename, "<?php\n");
       
        }
    }

    public static function getData()
    {
        $data['space'] = self::$namespace;
        $data['usenamespace'] = self::$usenamespace;
        $data['class'] = self::$modelName;
        $data['extends'] = self::$extends;
        $data['tablename2'] = self::$tablename2;
        $data['initialize'] = self::$initialize; 
        $data['pk2'] = self::$pk2;
        $data['softdel'] = self::$softdel;
        $data['basequery'] = self::$basequery;
        $data['create_update_time2'] = self::$create_update_time2;
        $data['setAttr'] = self::$setAttr;
        $data['getAttr'] = self::$getAttr;
        $data['dataComplete'] = self::$dataComplete;
        $data['allowFiled2'] = self::$allowFiled2;
        $data['CtlAllowField'] = self::$CtlAllowField;
        $data['error'] = self::$error;
        $data['validate2'] = self::$validate2;
        // $data['OOguanlian'] = self::$OOguanlian;
        // $data['OTguanlian'] = self::$OTguanlian;
        // $data['belongsTo2'] = self::$belongsTo2;
        return $data;
    }
    public static function getControllerPage()
    {

       $data = self::getData();
        $page = "<?php
namespace {$data['space']};

{$data['usenamespace']}
class {$data['class']} extends {$data['extends']}
{
    /**
     * 1.新增接口static::save() | POST
     *
     * 2.删除接口static::delete() | DELETE
     *
     * 3.修改接口 static::update() | PUT
     *
     * 4.查询接口
     *   读取指定id数据的接口 static::read() | GET
     * 5.根据页码获取数据，
     */
    public function save(Request \$request)
    {   
        \${$data['class']}Service = \$this->get{$data['class']}Service();
        \$result = \${$data['class']}Service{$data['CtlAllowField']['save']};
        if(is_array(\$result))return \$this->responseSuccess(\$result);
        return \$this->responseError({$data['error']},\$result);
    }

    
}";
        return $page;
    }

    public static function getServicePage()
    {

        $space = self::$namespace;
        $usenamespace = self::$usenamespace;
        $class = self::$modelName;
        $extends = self::$extends;
        $tablename2 = self::$tablename2;
        $initialize = self::$initialize; 
        $pk2 = self::$pk2;
        $softdel = self::$softdel;
        $basequery = self::$basequery;
        $create_update_time2 = self::$create_update_time2;
        $setAttr = self::$setAttr;
        $getAttr = self::$getAttr;
        $dataComplete = self::$dataComplete;
        $usenamespace = self::$usenamespace;
$page = "<?php 
namespace {$space};
{$usenamespace}
class {$class}Service extends {$extends}
{
    /**
     * 服务层
     * 1：一个新增修改服务static::save() 方法
     *      根据是否传递id可以进行区别是新增还是修改
     * 2：一个根据id删除服务，还要删除子类，只在开发期间使用 satatic::delete()
     * 3：两个读取服务
     *      读取自己和一层子数据 static::getChildList()
     *      读取自己和所有子数据 static::getAllList()
     */

    /**
    * 传入pid，返回该pid下一层子数据
    */
    public function save(\$data,\$id=null)
    {   
        \$articleCateModel = new ArticleCate;
        if(\$id === null){
            \$result = \$articleCateModel->saveCate(\$data);
        }else{
            \$result = \$articleCateModel->updateCate(\$data['cat_id'],\$data);
        }
        return \$result;
    }

    public function delete(\$id)
    {
        \$articleCateModel = new ArticleCate;
        return \$articleCateModel->destroyChildren(\$id);
    }

    public function getAllList(\$id)
    {
        \$articleCateModel = new ArticleCate;
        return \$articleCateModel->getAllList(\$id);
    } 

    public function getChildList(\$id)
    {
        \$articleCateModel = new ArticleCate;
        return \$articleCateModel->getChildList(\$id);
    }

    public function read(\$id)
    {
        \$articleCateModel = new ArticleCate;  
        return \$articleCateModel->getMyself(\$id);  
    }
    public static function tree()
    {
        \$articleCateModel = new ArticleCate();
        \$result = \$articleCateModel->select()->toArray();
        if(\$result){
            \$tree = new Tree();
            \$res  = \$tree->make_tree(\$result,'cat_id','parent_id','children');
            return \$res;

        }else{
            self::setError(\$articleCateModel->getError());
            return false;
        }

    }
}";
        return $page;
    }

    public static function getModelPage()
    {
        $space = self::$namespace;
        $usenamespace = self::$usenamespace;
        $class = self::$modelName;
        $extends = self::$extends;
        $tablename2 = self::$tablename2;
        $initialize = self::$initialize; 
        $pk2 = self::$pk2;
        $softdel = self::$softdel;
        $basequery = self::$basequery;
        $create_update_time2 = self::$create_update_time2;
        $setAttr = self::$setAttr;
        $getAttr = self::$getAttr;
        $dataComplete = self::$dataComplete;
        $usenamespace = self::$usenamespace;

        $content = "<?php\nnamespace {$space};\n\nclass {$class} extends {$extends}\n{\n
    {$tablename2}\n
    {$pk2}\n
    {$initialize}\n
    {$softdel}\n
    {$basequery}\n
    {$create_update_time2}\n
    {$setAttr}\n
    {$getAttr}\n
    {$dataComplete}\n
                                
                                
                                \n}";
    }

    
    
}