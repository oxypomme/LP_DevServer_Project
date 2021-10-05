<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UpdateUser extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->find((int) $args['user_id']);

    $user->username = (string) $parsedBody['username'];
    $user->password = (string) $parsedBody['password'];
    $user->email = (string) $parsedBody['email'];
    $user->phone = (string) $parsedBody['phone'];
    $user->birthdate = new \DateTime((string) $parsedBody['birthdate']);
    $user->address = (string) $parsedBody['address'];
    $user->city = (string) $parsedBody['city'];
    $user->country = (string) $parsedBody['country'];
    $user->status = (int) $parsedBody['status'];

    $this->em->persist($user);
    $this->em->flush();

    return $this->createResponse($user);
  }
}
