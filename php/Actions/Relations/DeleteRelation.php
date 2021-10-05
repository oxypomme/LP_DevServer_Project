<?php

namespace Crisis\Actions\Relations;

use Crisis\Models\Relation;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DeleteRelation extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Relation $relation */
    $relation = $this->em
      ->getRepository(Relation::class)
      ->find((int) $args['relation_id']);

    $this->em->remove($relation);
    $this->em->flush();

    return $this->createResponse();
  }
}
