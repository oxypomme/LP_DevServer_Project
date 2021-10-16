<?php

namespace Crisis\Actions\Groups\Members;

use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class GetMember extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $this->checkUser($request, (int) $args['user_id']);

    /** @var Group $group */
    $group = $this->em
      ->getRepository(Group::class)
      ->find((int) $args['group_id']);

    foreach ($group->getMembers() as $member) {
      if ($member->id == (int) $args['member_id']) {
        return $this->createResponse($member);
      }
    }

    throw new HttpException($request, 'Group not found', 404);
  }
}
