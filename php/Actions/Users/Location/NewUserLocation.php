<?php

namespace Crisis\Actions\Users\Location;

use Crisis\Models\User;
use Crisis\Models\Location;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class NewUserLocation extends ProtectedInvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    // Check authorisations
    $this->checkUser($request, (int) $args['user_id']);

    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    $location = new Location(
      (float) $parsedBody['long'],
      (float) $parsedBody['lat'],
      $user
    );
    $this->em->persist($location);
    $this->em->flush();

    return $this->createResponse($location);
  }
}
