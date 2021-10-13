<?php

namespace Crisis\Actions\Groups;

use Crisis\Models\Group;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetGroup extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // TODO?: Visible only by owner/members ?

    /** @var Group $group */
    $group = $this->em
      ->getRepository(Group::class)
      ->find((int) $args['group_id']);

    return $this->createResponse($group);
  }
}
