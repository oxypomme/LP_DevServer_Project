<?php

namespace Crisis\Actions;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

abstract class InvokableAction
{
  abstract public function handle(Request $request, Response $response, array $args): Response;

  public function getParsedBody(Request &$request): array
  {
    return json_decode($request->getBody()->getContents(), true);
  }

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    return $this->handle($request, $response, $args);
  }
}
