<?php

namespace Crisis\Actions\Users\Location;

use Crisis\Models\User;
use Crisis\Models\Location;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class UpdateUserLocation extends ProtectedInvokableEMAction
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

    $location = $user->getLocation()->update(
      (float) $parsedBody['long'],
      (float) $parsedBody['lat']
    );
    $this->em->persist($location);
    $this->em->flush();

    return $this->createResponse($location);
  }
}
