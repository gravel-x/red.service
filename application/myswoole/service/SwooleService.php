<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/17
 * Time: 17:06
 */
namespace app\myswoole\service;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\service\PushService;

class SwooleService extends Command
{
    public static function test(Input $input, Output $output)
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