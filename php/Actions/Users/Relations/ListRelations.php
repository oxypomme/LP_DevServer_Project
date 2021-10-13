<?php

namespace Crisis\Actions\Users\Relations;

use Crisis\Models\User;
use Crisis\Models\Relation;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ListRelations extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], (int) $args['user_id'])) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    $res = [
      'outRelations' => [],
      'inRelations' => []
    ];
    foreach ($user->outRelations as $relation) {
      $res['outRelations'][] = $relation;
    }
    foreach ($user->inRelations as $relation) {
      $res['inRelations'][] = $relation;
    }

    return $this->createResponse($res);
  }
}
