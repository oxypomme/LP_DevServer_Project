<?php

namespace Crisis\Actions\Users\Relations;

use Crisis\Models\User;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class GetRelation extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $this->checkUser($request, (int) $args['user_id']);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    foreach ($user->getMergedRelations() as $relation) {
      if ($relation->id == (int) $args['relation_id']) {
        return $this->createResponse($relation);
      }
    }

    throw new HttpException($request, 'Relation not found', 404);
  }
}
