<?php

namespace Crisis\Actions\Users\Relations;


use Crisis\Models\Relation;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetRelation extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Relation $relation */
    $relation = $this->em
      ->getRepository(Relation::class)
      ->find((int) $args['relation_id']);

    return $this->createResponse($this->getFullObject($relation));
  }
}
