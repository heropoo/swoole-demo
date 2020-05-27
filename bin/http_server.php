<?php

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

//  实例化一个http服务器，监听本地的9501端口
$server = new Server("127.0.0.1", 9501);  // 如果想要监听全局，ip换成 0.0.0.0 即可

// 处理request事件
$server->on('request', function (Request $request, Response $response) {
    // 设置响应头
    $response->header("Content-Type", "text/html; charset=utf-8");
    // 设置响应内容
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

// 处理start事件
$server->on("start", function (Server $server) {
    echo "Swoole http server is started at http://127.0.0.1:9501\n";
});

// 启动 HTTP 服务器
$server->start();