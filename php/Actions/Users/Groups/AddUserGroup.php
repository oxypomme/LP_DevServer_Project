<?php

namespace Crisis\Actions\Users\Groups;

use Crisis\Models\User;
use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AddUserGroup extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], (int) $args['user_id'])) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    /** @var Group $group */
    $group = $this->em
      ->getRepository(Group::class)
      ->find((int) $parsedBody['group_id']);

    if (!is_null($group)) {
      $group->addToGroup($user);
      $this->em->persist($group);
      $this->em->flush();
      return $this->createResponse($this->getFullObject($group));
    }

    return $this->createResponse(['status' => 404, 'message' => 'Group not found'], 404);
  }
}
