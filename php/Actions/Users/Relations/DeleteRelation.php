<?php

namespace Crisis\Actions\Users\Relations;

use Crisis\Models\Relation;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class DeleteRelation extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Relation $relation */
    $relation = $this->em
      ->getRepository(Relation::class)
      ->find((int) $args['relation_id']);

    // Check authorisations
    $this->checkUser($request, $relation->getSender()->id);

    try {
      $relation->getSender()->removeOutRelation($relation);
      $relation->getTarget()->removeInRelation($relation);

      $this->em->remove($relation);
      $this->em->flush();
    } catch (\Exception $e) {
      $this->em->rollback();
      throw $e;
    }

    return $this->createResponse('OK');
  }
}
