<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NewUser extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    $parsedBody = $this->getParsedBody($request);

    $user = new User(
      (string) $parsedBody['username'],
      (string) $parsedBody['password'],
      (string) $parsedBody['email'],
      (string) $parsedBody['phone'],
      new \DateTime((string) $parsedBody['birthdate']),
      (string) $parsedBody['address'],
      (string) $parsedBody['city'],
      (string) $parsedBody['country']
    );
    $this->em->persist($user);
    $this->em->flush();

    return $this->createResponse($user);
  }
}
