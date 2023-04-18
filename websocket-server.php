<?php
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;



class RandomNumberWebSocket implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }


    public function onClose(ConnectionInterface $conn)
    {
        echo "Connection closed ({$conn->resourceId})\n";
        if (isset($this->timers[$conn->resourceId])) {
            $this->loop->cancelTimer($this->timers[$conn->resourceId]);
            unset($this->timers[$conn->resourceId]);
        }
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error occurred: " . $e->getMessage() . " in file " . $e->getFile() . " on line " . $e->getLine() . "\n";
        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (isset($data['qr'])) {
            $qr = $data['qr'];
            echo "Received new QR code: $qr\n";
            $this->sendToAllClients($msg); // отправляем сообщение всем клиентам
        }
    }

    public function sendToAllClients($msg)
    {
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

//    public function onMessage(ConnectionInterface $from, $msg)
//    {
//        $data = json_decode($msg, true);
//        if (isset($data['id'])) {
//            $id = $data['id'];
//
//            if ($id == '88') {
//                $qr = $data['qr'];
//                echo "Received new QR code: $qr\n";
//                $message = json_encode([
//                    'qr' => $qr
//                ]);
//                foreach ($this->clients as $client) {
//                    $client->send($message);
//                }
//            }
//        }
//    }

//    public function onMessage(ConnectionInterface $from, $msg)
//    {
//        $data = json_decode($msg, true);
//        $qr = $data['qr'];
//        echo "Received new QR code: $qr\n"; // добавленная строка
//    }
}

$loop = React\EventLoop\Factory::create();
$randomNumberWebSocket = new RandomNumberWebSocket();

$server = IoServer::factory(
    new HttpServer(
        new WsServer($randomNumberWebSocket)
    ),
    8080
);

$server->run();
