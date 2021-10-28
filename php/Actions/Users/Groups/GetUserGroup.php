<?php

namespace Crisis\Actions\Users\Groups;

use Crisis\Models\User;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class GetUserGroup extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $this->checkUser($request, (int) $args['user_id']);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    foreach ($user->getMergedGroups() as $group) {
      if ($group->id == (int) $args['group_id']) {
        return $this->createResponse($group);
      }
    }

    throw new HttpException($request, 'Group not found', 404);
  }
}
