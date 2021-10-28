<?php

namespace Crisis\Actions\Groups\Members;


use Crisis\Models\User;
use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class DeleteMember extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Group $group */
    $group = $this->em
      ->getRepository(Group::class)
      ->find((int) $args['group_id']);

    // Check authorisations
    $this->checkUser($request, $group->getOwner()->id);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    if (!is_null($user)) {
      $group->removeToGroup($user);
      $this->em->persist($group);
      $this->em->flush();

      return $this->createResponse($group);
    }

    throw new HttpException($request, 'Member not found', 404);
  }
}
