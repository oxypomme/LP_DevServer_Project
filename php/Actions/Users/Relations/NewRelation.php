<?php

namespace Crisis\Actions\Users\Relations;

use Crisis\Models\User;
use Crisis\Models\Relation;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class NewRelation extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $jwtPayload = (new \PsrJwt\Helper\Request())->getTokenPayload($request, 'jwt');
    if (!$this->checkUser((int) $jwtPayload['user_id'], (int) $args['user_id'])) {
      throw new HttpException($request, 'Unauthorized', 401);
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

      return $this->createResponse($relation);
    }

    throw new HttpException($request, 'Target not found', 404);
  }
}
