<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/29
 * Time: 16:41
 */

namespace app\Console;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class WebSocket extends Command
{
    // Server 实例
    protected $server;

    protected function configure()
    {
        $this->setName('websocket:start')->setDescription('Start Web Socket Server!');
    }

    protected function execute(Input $input, Output $output)
    {
        // 监听所有地址，监听 10000 端口
        $this->server = new \swoole_websocket_server('0.0.0.0', 35000);

//         设置 service 运行前各项参数
//         调试的时候把守护进程关闭，部署到生产环境时再把注释取消
         $this->server->set([
             'daemonize' => true,
         ]);

        // 设置回调函数
        $this->server->on('Open', [$this, 'onOpen']);
        $this->server->on('Message', [$this, 'onMessage']);
        $this->server->on('Close', [$this, 'onClose']);

        $this->server->start();
        // $output->writeln("WebSocket: Start.\n");
    }

    // 建立连接时回调函数
    public function onOpen(\swoole_websocket_server $server, $request)
    {
        echo "service: handshake success with fd{$request->fd}\n";
    }

    // 收到数据时回调函数
    public function onMessage(\swoole_websocket_server $server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $server->push($frame->fd, "this is service");
    }

    // 连接关闭时回调函数
    public function onClose($server, $fd)
    {
        echo "client {$fd} closed\n";
    }
}