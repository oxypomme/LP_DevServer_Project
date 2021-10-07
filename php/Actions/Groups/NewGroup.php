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
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'])) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $jwtPayload['user_id']);

    $group = new Group(
      (string) $parsedBody['name'],
      $user
    );
    $this->em->persist($group);
    $this->em->flush();

    // TODO: missing id

    return $this->createResponse($group);
  }
}
