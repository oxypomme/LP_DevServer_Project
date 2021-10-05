<?php

namespace Crisis\Actions\Groups;

use Crisis\Models\User;
use Crisis\Models\Group;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetGroup extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Group[] $groups */
    $groups = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id'])
      ->groups;

    $result = null;
    foreach ($groups as $key => $group) {
      if ($group->id == (int) $args['group_id']) {
        $result = $group;
      }
    }

    return $this->createResponse($result);
  }
}
