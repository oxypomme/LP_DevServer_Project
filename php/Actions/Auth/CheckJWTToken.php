<?php

namespace Crisis\Actions\Auth;

use Crisis\Actions\InvokableJSONAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CheckJWTToken extends InvokableJSONAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    return $this->createResponse('OK');
  }
}
