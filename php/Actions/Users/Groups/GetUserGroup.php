<?php

namespace Crisis\Actions\Users\Groups;

use Crisis\Models\User;
use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetUserGroup extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], (int) $args['user_id'])) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    /** @var Group[] $rawGroups */
    $rawGroups = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id'])
      ->groups;

    foreach ($rawGroups as $group) {
      if ($group->id == (int) $args['group_id']) {
        return $this->createResponse($this->getFullObject($group));
      }
    }

    return $this->createResponse(['status' => 404, 'message' => 'Group not found'], 404);
  }
}
