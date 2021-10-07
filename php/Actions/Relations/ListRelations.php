<?php

namespace Crisis\Actions\Relations;

use Crisis\Models\User;
use Crisis\Models\Relation;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ListRelations extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Relation[] $relations */
    $relations = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id'])
      ->relations;

    $res = [];
    foreach ($relations as $relation) {
      $res[] = $this->getFullObject($relation);
    }

    return $this->createResponse($res);
  }
}
