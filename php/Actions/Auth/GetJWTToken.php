<?php

namespace Crisis\Actions\Auth;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;

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

        $now = new \DateTime();
        $expiration = (clone $now)->add(new \DateInterval('PT2H'));

        $token = $builder->setSecret($_ENV['JWT_SECRET'])
          ->setPayloadClaim('user_id', $user->id)
          ->setIssuedAt($now->getTimestamp())
          ->setExpiration($expiration->getTimestamp())
          ->build();

        return $this->createResponse([
          'token' => $token->getToken(),
          'created_at' => $now->format('c'),
          'expires' => $expiration->format('c')
        ]);
      };
    }

    throw new HttpException($request, 'Bad Creditentials', 401);
  }
}
