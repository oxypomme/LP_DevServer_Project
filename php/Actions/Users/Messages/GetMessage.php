<?php

namespace Crisis\Actions\Users\Messages;

use Crisis\Models\User;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class GetMessages extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $this->checkUser($request, (int) $args['user_id']);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    foreach ($user->getMergedMessages() as $message) {
      if ($message->id == (int) $args['message_id']) {
        return $this->createResponse($message);
      }
    }

    throw new HttpException($request, 'Message not found', 404);
  }
}
