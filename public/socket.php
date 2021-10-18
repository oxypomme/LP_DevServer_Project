<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use \Crisis\JSON;
use Crisis\Models\Group;
use \Crisis\Models\User;
use \Crisis\Models\Message;

class ServerImpl implements MessageComponentInterface
{
  /** @var \SplObjectStorage<ConnectionInterface, User> $clients */
  protected \SplObjectStorage $clients;

  protected \Doctrine\ORM\EntityManager $em;

  protected array $settings;

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->clients = new \SplObjectStorage;
    $this->em = $cnt->get(\Doctrine\ORM\EntityManager::class);
    $this->settings = $cnt->get('settings');
  }

  public function checkJWT(string $jwt): ?User
  {
    $factory = new \PsrJwt\Factory\Jwt();
    $parser = $factory->parser($jwt, $this->settings['jwt']['secret']);
    try {
      $parser->validate();
    } catch (\ReallySimpleJWT\Exception\ValidateException $th) {
      return null;
    }
    $parsed = $parser->parse();

    $auth_user_id = (int) $parsed->getSubject();
    $user = $this->em
      ->getRepository(User::class)
      ->find($auth_user_id);
    if (!is_null($user)) {
      return $user;
    }
    return null;
  }

  public function sendMessage(string $data, ?User $target = null, ?Group $group = null)
  {
    foreach ($this->clients as $client => $user) {
      if (
        // Search for specific target
        (!is_null($target) && $user->id == $target->id)
        // Search for group of targets
        || (!is_null($group) && $user->inGroup($group))
      ) {
        $client->send($data);

        // If specific target, no need to continue
        if (!is_null($target)) {
          break;
        }
      }
    }
  }

  public function onOpen(ConnectionInterface $conn)
  {
    // $this->clients->attach($conn);
    echo "New connection! ({$conn->resourceId}).\n";
  }

  public function onMessage(ConnectionInterface $conn, $inMsg)
  {
    $event = json_decode($inMsg);
    $sender = $this->checkJWT($event->jwt);
    if (is_null($sender->id)) {
      $conn->send(JSON::encode([
        'type' => "error",
        "payload" => "Auth Failed"
      ]));
      return;
    }
    unset($event->jwt);

    if (!$this->clients->contains($conn)) {
      $this->clients->attach($conn, $sender);
    }

    echo sprintf("New message from '%s': %s\n\n", $conn->resourceId, JSON::encode($event));
    switch ($event->type) {
      case 'ping':
        $conn->send(JSON::encode($event));
        break;

      case 'message':
        /** @var ?User $target */
        $target = null;
        if (!is_null($event->payload->target)) {
          $target = $this->em
            ->getRepository(User::class)
            ->find((int) $event->payload->target);
        }

        /** @var ?Group $group */
        $group = null;
        if (!is_null($event->payload->group)) {
          $group = $this->em
            ->getRepository(Group::class)
            ->find((int) $event->payload->group);
        }

        $msg = new Message(
          (string) $event->payload->content,
          (string) $event->payload->attachement,
          $sender,
          $target,
          $group
        );

        //TODO If target & group null

        $this->em->persist($msg);
        $this->em->flush();

        $data = [
          'type' => 'message',
          'payload' => $msg
        ];

        $this->sendMessage(JSON::encode($data), $target, $group);
        break;

      case 'message_edit':
        /** @var Message $msg */
        $msg = $this->em
          ->getRepository(Message::class)
          ->find((int) $event->payload->id);

        if ($msg->getSender()->id != $sender->id) {
          $conn->send(JSON::encode([
            'type' => "error",
            "payload" => "Not Authorized"
          ]));
          return;
        }

        $msg->content = (string) $event->payload->content;
        $msg->attachement = (string) $event->payload->attachement;
        $msg->updated_at = new \DateTime();

        $this->em->persist($msg);
        $this->em->flush();

        $data = [
          'type' => 'message_edit',
          'payload' => $msg
        ];

        $this->sendMessage(JSON::encode($data), $msg->getTarget(), $msg->getGroup());
        break;

      case 'message_deletion':
        /** @var Message $msg */
        $msg = $this->em
          ->getRepository(Message::class)
          ->find((int) $event->payload->id);

        if ($msg->getSender()->id != $sender->id) {
          $conn->send(JSON::encode([
            'type' => "error",
            "payload" => "Not Authorized"
          ]));
          return;
        }

        $target = $msg->getTarget();
        $group = $msg->getGroup();

        try {
          $msg->getSender()->removeOutMessage($msg);
          $target->removeInMessage($msg);
          $group->removeMessage($msg);

          $this->em->remove($msg);
          $this->em->flush();
        } catch (\Throwable $th) {
          $this->em->rollback();
          $conn->send(JSON::encode([
            'type' => "error",
            "payload" => $th->getMessage()
          ]));
          throw $th;
        }

        $data = [
          'type' => 'message_deletion',
          'payload' => $msg
        ];

        $this->sendMessage(JSON::encode($data), $target, $group);
        break;

      default:
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
