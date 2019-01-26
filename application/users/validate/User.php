<?php
/**
 * Created by PhpStorm.
 * User: greatsir
 * Date: 2018/1/26
 * Time: 下午4:30
 */
namespace app\users\validate;
use think\Validate;

class User extends Validate
{
    protected $rule=[
        'user_id'=>'require'
    ];

}