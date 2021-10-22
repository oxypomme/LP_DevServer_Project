<?php

namespace Crisis\Actions\Groups\Messages;

use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class GetGroupMessage extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    //? Visible only by owner/members

    /** @var Group $group */
    $group = $this->em
      ->getRepository(Group::class)
      ->find((int) $args['group_id']);

    foreach ($group->getMessages() as $message) {
      if ($message->id == (int) $args['message_id']) {
        return $this->createResponse($message);
      }
    }

    throw new HttpException($request, 'Message not found', 404);
  }
}
