<?php

namespace Crisis\Actions\Users\Relations;

use Crisis\Models\Relation;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DeleteRelationextends extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    /** @var Relation $relation */
    $relation = $this->em
      ->getRepository(Relation::class)
      ->find((int) $args['relation_id']);

    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], $relation->sender->id)) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    try {
      //TODO: Remove relation from users
      $this->em->remove($relation);
      $this->em->flush();
    } catch (\Exception $e) {
      $this->em->rollback();
      throw $e;
    }

    return $this->createResponse(['status' => 200, 'message' => 'OK']);
  }
}
