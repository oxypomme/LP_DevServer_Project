<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use \Crisis\JSON;

class ServerImpl implements MessageComponentInterface
{
  /** @var \SplObjectStorage<ConnectionInterface> $clients */
  protected \SplObjectStorage $clients;

  protected \Doctrine\ORM\EntityManager $em;

  protected array $settings;

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->clients = new \SplObjectStorage;
    $this->em = $cnt->get(\Doctrine\ORM\EntityManager::class);
    $this->settings = $cnt->get('settings');
  }

  public function checkJWT(string $jwt): bool
  {
    $factory = new \PsrJwt\Factory\Jwt();
    $parser = $factory->parser($jwt, $this->settings['jwt']['secret']);
    try {
      $parser->validate();
    } catch (\ReallySimpleJWT\Exception\ValidateException $th) {
      return false;
    }
    $parsed = $parser->parse();

    $auth_user_id = (int) $parsed->getSubject();
    $user = $this->em
      ->getRepository(\Crisis\Models\User::class)
      ->find($auth_user_id);
    return !is_null($user);
  }

  public function onOpen(ConnectionInterface $conn)
  {
    $this->clients->attach($conn);
    echo "New connection! ({$conn->resourceId}).\n";
  }

  public function onMessage(ConnectionInterface $conn, $inMsg)
  {
    $event = json_decode($inMsg);
    // TODO check $event->jwt
    if (!$this->checkJWT($event->jwt)) {
      $conn->send(JSON::encode([
        'type' => "error",
        "payload" => "Auth Failed"
      ]));
      return;
    }
    unset($event->jwt);

    $msg = JSON::encode($event);
    echo sprintf("New message from '%s': %s\n\n\n", $conn->resourceId, $msg);
    switch ($event->type) {
      case 'ping':
        $conn->send($msg);
        break;

      default:
        foreach ($this->clients as $client) { // BROADCAST
          // if ($conn !== $client) {
          $client->send($msg);
          // }
        }
        break;
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
