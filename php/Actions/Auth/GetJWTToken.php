<?php

namespace Crisis\Actions\Auth;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use DateTime;
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
      if ($user->checkPassword($parsedBody['password'])) {
        $factory = new \PsrJwt\Factory\Jwt();

        $builder = $factory->builder();

        $now = new DateTime();
        $expiration = (clone $now)->add(new \DateInterval('PT2H'));

        $token = $builder->setSecret($_ENV['JWT_SECRET'])
          ->setPayloadClaim('user_id', $user->id)
          ->setIssuedAt($now->getTimestamp())
          ->setExpiration($expiration->getTimestamp())
          ->build();

        return $this->createResponse([
          'status' => 200,
          'token' => $token->getToken(),
          'issued' => $now->format('c'),
          'expires' => $expiration->format('c')
        ]);
      };
    }

    return $this->createResponse(['status' => 401, 'message' => 'Bad Creditentials'], 401);
  }
}
