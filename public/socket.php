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

  public function sendMessage(string $data, ?User $target = null, ?Group $group = null): void
  {
    foreach ($this->clients as $value) {
      /** @var ConnectionInterface $client */
      $client = $this->clients->current();
      /** @var User $user */
      $user = $this->clients->getInfo();

      if (
        // Search for specific target
        (!is_null($target) && $user->id == $target->id)
        // Search for group of targets
        || (!is_null($group) && $user->inGroup($group))
      ) {
        $client->send($data);

        // If specific target, no need to continue
        if (!is_null($target)) {
          return;
        }
      }
    }
    // Throw error if target is not connected
    if (!is_null($target)) {
      throw new Error("No target found");
    }
  }

  public function onOpen(ConnectionInterface $conn)
  {
    // $this->clients->attach($conn);
    echo "\nNew connection! ({$conn->resourceId}).\n\n";
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

    // If first time connection
    if (!$this->clients->contains($conn)) {
      $this->clients->attach($conn, $sender);
      // Notify his friend that he's connected
      $data = [
        'type' => 'connection_in',
        'payload' => [
          'id' => $sender->id
        ]
      ];
      $rels = $sender->getRelations();
      $relations = [];
      foreach ($rels['relations'] as $rel) {
        /** @var \Crisis\Models\Relation $rel */
        $obj = $rel->jsonSerialize();
        try {
          $this->sendMessage(JSON::encode($data), $rel->getTarget());
          $obj['isLogged'] = true;
        } catch (\Throwable $th) {
          $obj['isLogged'] = false;
        }
        $relations[] = $obj;
      }
      // Notify him of his friends state
      $rels['relations'] = $relations;
      $conn->send(JSON::encode([
        'type' => 'friends',
        'payload' => $rels
      ]));
    }

    echo sprintf("\nNew message from '%s': %s\n\n", $conn->resourceId, JSON::encode($event));
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

        $data = [
          'type' => 'message',
          'payload' => $msg
        ];

        try {
          $this->sendMessage(JSON::encode($data), $target, $group);

          $this->em->persist($msg);
          $this->em->flush();
        } catch (\Throwable $th) {
          // TODO: Error management
          throw $th;
        }

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

        $data = [
          'type' => 'message_edit',
          'payload' => $msg
        ];

        try {
          $this->sendMessage(JSON::encode($data), $msg->getTarget(), $msg->getGroup());

          $this->em->persist($msg);
          $this->em->flush();
        } catch (\Throwable $th) {
          // TODO: Error management
          throw $th;
        }
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

        $data = [
          'type' => 'message_deletion',
          'payload' => $msg
        ];

        try {
          $this->sendMessage(JSON::encode($data), $target, $group);
        } catch (\Throwable $th) {
          // TODO: Error management
          throw $th;
        }

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
        break;

      default:
        break;
    }
  }

  public function onClose(ConnectionInterface $conn)
  {
    $sender = $this->clients[$conn];
    $data = [
      'type' => 'connection_out',
      'payload' => [
        'id' => $sender->id
      ]
    ];
    foreach ($sender->getRelations()['relations'] as $rel) {
      /** @var \Crisis\Models\Relation $rel */
      try {
        $this->sendMessage(JSON::encode($data), $rel->getTarget());
      } catch (\Throwable $th) {
        continue;
      }
    }

    $this->clients->detach($conn);
    echo "\nConnection {$conn->resourceId} is gone.\n\n";
  }

  public function onError(ConnectionInterface $conn, \Exception $e)
  {
    echo "\n\nAn error occured on connection {$conn->resourceId}: {$e->getMessage()}\n\n";
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
echo "\nServer created on port $port\n\n";
$server->run();
