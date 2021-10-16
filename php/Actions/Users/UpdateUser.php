<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\ProtectedInvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

class UpdateUser extends ProtectedInvokableEMAction
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

    $user->username = (string) $parsedBody['username'];
    $user->setPassword((string) $parsedBody['password']);
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
