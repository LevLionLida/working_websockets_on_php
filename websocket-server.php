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
        if (isset($data['Id'])) {
            $this->clientIds[$from->resourceId] = $data['Id'];
//            echo "Received clientId: {$data['Id']} from connection ({$from->resourceId})\n";
        }
        if (isset($data['qr']) && isset($data['clientId'])) {
            $qr = $data['qr'];
            $clientId = $data['clientId'];
            echo "Received new QR code: $qr from clientId: $clientId\n";
        }
// Send the QR code to clients with matching ID
        foreach ($this->clients as $client) {
            $clientResourceId = $client->resourceId;
            //"если у нас есть ID клиента для этого подключения И этот ID  равен  $clientId".
            if (isset($this->clientIds[$clientResourceId]) && $this->clientIds[$clientResourceId] == $clientId) {
                $client->send($msg);
            }else{
                $client->send('id не совпадают');
            }
        }
    }

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
