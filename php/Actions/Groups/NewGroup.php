<?php

namespace Crisis\Actions\Groups;

use Crisis\Models\User;
use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NewGroup extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $user_id = $this->checkUser($request);

    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find($user_id);

    $group = new Group(
      (string) $parsedBody['name'],
      $user
    );
    $this->em->persist($group);
    $this->em->flush();

    return $this->createResponse($group);
  }
}
