<?php

namespace Crisis\Handlers;

// An example JWT Authorisation Handler.
use PsrJwt\Auth\Authorise;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;

class JWTHandler extends Authorise implements RequestHandlerInterface
{
  public function __construct(string $secret)
  {
    parent::__construct($secret, '');
  }

  public function handle(ServerRequestInterface $request): ResponseInterface
  {
    $auth = $this->authorise($request);

    $error = explode(':', $auth->getMessage())[1];

    return new Response(
      $auth->getCode(),
      [
        'WWW-Authenticate' => 'Bearer'
      ],
      \Crisis\JSON::encode(
        ['status' => 401, 'payload' => $error ? trim($error) : $auth->getMessage()]
      )
    );
  }
}
