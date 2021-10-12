<?php

namespace Crisis\Actions\Users\Relations;

use Crisis\Models\User;
use Crisis\Models\Relation;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NewRelation extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], (int) $args['user_id'])) {
      return $this->createResponse(['status' => 401, 'message' => 'Unauthorized'], 401);
    }

    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    /** @var User $user */
    $target = $this->em
      ->getRepository(User::class)
      ->find((int) $parsedBody['target_id']);

    if (!is_null($target)) {
      $relation = new Relation(
        $user,
        $target
      );
      $this->em->persist($relation);
      $this->em->flush();

      // TODO: missing id

      return $this->createResponse($relation);
    }

    return $this->createResponse(['status' => 404, 'message' => 'Target not found'], 404);
  }
}
