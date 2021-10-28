<?php

namespace Crisis\Actions;

use Crisis\Actions\InvokableEMAction;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NotImplementedAction extends InvokableEMAction
{
  public function handle(Request $request, Response $response, array $args): Response
  {
    throw new \Error("NotImplementedError");
    return $this->createResponse();
  }
}
