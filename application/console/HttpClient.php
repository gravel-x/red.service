<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/29
 * Time: 16:16
 */
namespace app\Console;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class HttpClient extends Command
{
    // 命令行配置函数
    public function configure()
    {
        // setName 设置命令行名称
        // setDescription 设置命令行描述
        $this->setName('http:client')->setDescription('Start Http Client!');
    }

    // 设置命令返回信息
    public function execute(Input $input, Output $output)
    {
        // 将域名解析为IP地址
        \swoole_async_dns_lookup("freeapi.ipip.net", function ($domainName, $ip) {
            // 实例化 swoole_http_client
            $cli = new \swoole_http_client($ip, 80);


            // 设置 Http 请求头
            $cli->setHeaders([
                'Host'            => $domainName,
                "User-Agent"      => 'Chrome/49.0.2587.3',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml',
                'Accept-Encoding' => 'gzip',
            ]);

            // 发起GET请求
            $cli->get('/202.97.224.68', function ($cli) {

                // 统计返回内容长度
                echo "Length: " . strlen($cli->body) . "\n";
                // 显示返回内容
                echo $cli->body;

                // 关闭请求
                $cli->close();
            });
        });

        $output->writeln("HttpClient: Start.\n");
    }
}