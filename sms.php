<?php
require 'vendor/autoload.php';
use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector;
use React\EventLoop\Loop;

function sendMessage($id, array $message) {
    $loop = Loop::get();
    $connector = new Connector($loop);
    $connector('ws://localhost:8080')->then(function (WebSocket $conn) use ($id, $message) {

        $message['clientId'] = $id; // Добавляем 'clientId' в конец массива $message

        $messageJson = json_encode($message);

        $conn->send($messageJson);
        echo "Message sent: $messageJson\n";
        $conn->close();
    }, function (\Exception $e) use ($loop) {
        echo "Could not connect: {$e->getMessage()}\n";
        $loop->stop();
    });

    $loop->run();
}

// Example usage
$clientId = '111';
$qr = mt_rand(100, 999); // Generate a random number between 100 and 999
$message = [
    'qr' => $qr,
];


sendMessage($clientId, $message);
