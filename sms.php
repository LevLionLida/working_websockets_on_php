<?php
require 'vendor/autoload.php';
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;

$loop = React\EventLoop\Factory::create();
$connector = new \Ratchet\Client\Connector($loop);
$connector('ws://localhost:8080')->then(function (WebSocket $conn) {
    $clientId ='88';

    $qr = mt_rand(100, 999); // генерация случайного числа от 100 до 999
    $message = json_encode([
        'qr' => $qr,
        'clientId'=> $clientId,
    ]);

    $conn->send($message);
    echo "Message sent: $message\n";
    $conn->close();
}, function (\Exception $e) use ($loop) {
    echo "Could not connect: {$e->getMessage()}\n";
    $loop->stop();
});

$loop->run();
