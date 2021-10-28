<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetCurrentUser extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $user_id = $this->checkUser($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find($user_id);

    return $this->createResponse($user);
  }
}
