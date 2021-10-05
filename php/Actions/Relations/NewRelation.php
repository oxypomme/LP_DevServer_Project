<?php

namespace Crisis\Actions\Relations;

use Crisis\Models\User;
use Crisis\Models\Relation;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NewRelation extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    /** @var User $user */
    $target = $this->em
      ->getRepository(User::class)
      ->find((int) $parsedBody['target_id']);

    $relation = new Relation(
      $user,
      $target
    );
    $this->em->persist($relation);
    $this->em->flush();

    // TODO: missing id

    return $this->createResponse($relation);
  }
}
