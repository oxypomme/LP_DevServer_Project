<?php

namespace Crisis\Actions\Groups;

use Crisis\Models\User;
use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UpdateGroup extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Group $group */
    $group = $this->em
      ->getRepository(Group::class)
      ->find((int) $args['group_id']);

    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], $group->getOwner()->id)) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $parsedBody['owner_id']);

    $group->setOwner($user);
    $group->name = (string) $parsedBody['name'];

    $this->em->persist($group);
    $this->em->flush();

    return $this->createResponse($group);
  }
}
