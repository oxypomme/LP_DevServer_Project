<?php

namespace Crisis\Actions;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

abstract class InvokableAction
{
  protected array $settings;

  public function __construct(\UMA\DIC\Container $cnt)
  {
    $this->settings = $cnt->get('settings');
  }

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
