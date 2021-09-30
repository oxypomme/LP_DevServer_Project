<?php

namespace Crisis\Actions\Auth;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetJWTToken extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    $parsedBody = $this->getParsedBody($request);

    /** @var User $user */
    $user = $this->em
      ->getRepository(User::class)
      ->findOneBy(['username' => (string) $parsedBody['username']]);

    if ($user) {
      if (password_verify($parsedBody['password'], $user->password)) {
        $factory = new \PsrJwt\Factory\Jwt();

        $builder = $factory->builder();

        $token = $builder->setSecret($_ENV['JWT_SECRET'])
          ->setPayloadClaim('user_id', $user->id)
          ->build();

        return $this->createResponse(['token' => $token->getToken()]);
      };
    }

    return $this->createResponse(['message' => 'Bad Creditentials'], 401);
  }
}
