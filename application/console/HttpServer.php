<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/29
 * Time: 15:59
 */

namespace app\Console;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class HttpServer extends Command
{
    protected $server;

    // 命令行配置函数
    protected function configure()
    {
        // setName 设置命令行名称 && setDescription 设置命令行描述
        $this->setName('http:service')->setDescription('Start Http Server!');
    }

    // 设置命令返回信息
    protected function execute(Input $input, Output $output)
    {

        $this->server = new \swoole_http_server("0.0.0.0", 39010);

        $this->server->on('request', function ($request, $response) {
            $response->end("<h1>Hello Myswoole. #".rand(1000, 9999)."</h1>");
        });

        $this->server->start();

        // $output->writeln("HttpServer: Start.\n");
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $data = isset($request->get) ? $request->get : '';

        $response->end(serialize($data));
    }
}