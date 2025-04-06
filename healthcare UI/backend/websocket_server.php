<?php
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class HealthMonitoringServer implements MessageComponentInterface {
    protected $clients;
    protected $subscriptions;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->subscriptions = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'subscribe':
                    $this->subscriptions[$from->resourceId] = $data['userId'];
                    break;
                case 'health_data':
                    $this->broadcastHealthData($data);
                    break;
            }
        }
    }

    protected function broadcastHealthData($data) {
        foreach ($this->clients as $client) {
            if (isset($this->subscriptions[$client->resourceId]) &&
                $this->subscriptions[$client->resourceId] === $data['userId']) {
                $client->send(json_encode($data));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->subscriptions[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$loop = Factory::create();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new HealthMonitoringServer()
        )
    ),
    8080,
    '0.0.0.0',
    $loop
);

echo "WebSocket server running on port 8080...\n";
$loop->run();