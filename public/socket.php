<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ServerImpl implements MessageComponentInterface
{
  protected $clients;

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->clients = new \SplObjectStorage;
    $this->em = $cnt->get(\Doctrine\ORM\EntityManager::class);
  }

  public function onOpen(ConnectionInterface $conn)
  {
    $this->clients->attach($conn);
    echo "New connection! ({$conn->resourceId}).\n";
  }

  public function onMessage(ConnectionInterface $conn, $msg)
  {
    echo sprintf("New message from '%s': %s\n\n\n", $conn->resourceId, $msg);
    foreach ($this->clients as $client) { // BROADCAST
      $message = json_decode($msg, true);
      if ($conn !== $client) {
        $client->send($msg);
      }
    }
  }

  public function onClose(ConnectionInterface $conn)
  {
    $this->clients->detach($conn);
    echo "Connection {$conn->resourceId} is gone.\n";
  }

  public function onError(ConnectionInterface $conn, \Exception $e)
  {
    echo "An error occured on connection {$conn->resourceId}: {$e->getMessage()}\n\n\n";
    $conn->close();
  }
}

$cnt = require_once __DIR__ . '/../bootstrap.php';

$cnt->register(new Crisis\Providers\Doctrine());

$port = $cnt->get('settings')['socket']['port'];
$server = IoServer::factory(
  new HttpServer(
    new WsServer(
      new ServerImpl($cnt)
    )
  ),
  $port
);
echo "Server created on port $port\n\n";
$server->run();
