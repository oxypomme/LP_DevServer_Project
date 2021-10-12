<?php

namespace Crisis\Actions\Groups\Members;

use Crisis\Models\Group;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ListMembers extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var User[] $rawMembers */
    $rawMembers = $this->em
      ->getRepository(Group::class)
      ->find((int) $args['group_id']);

    $members = [];
    foreach ($rawMembers as $member) {
      $members[] = \Crisis\Reflection::getFullObject($member, ['password']);
    }

    return $this->createResponse($members);
  }
}
