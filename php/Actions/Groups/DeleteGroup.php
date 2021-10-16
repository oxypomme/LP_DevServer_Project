<?php

namespace Crisis\Actions\Groups;

use Crisis\Models\Group;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DeleteGroup extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Group $group */
    $group = $this->em
      ->getRepository(Group::class)
      ->find((int) $args['group_id']);

    $this->checkUser($request, $group->getOwner()->id);

    try {
      foreach ($group->getMembers() as $member) {
        $member->removeGroup($group);
      }
      $group->getOwner()->removeOwnedGroup($group);

      $this->em->remove($group);
      $this->em->flush();
    } catch (\Exception $e) {
      $this->em->rollback();
      throw $e;
    }

    return $this->createResponse('OK');
  }
}
