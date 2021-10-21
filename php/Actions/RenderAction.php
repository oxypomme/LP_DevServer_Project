<?php

namespace Crisis\Actions;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class RenderAction
{
  private PhpRenderer $renderer;
  private string $template;
  private array $data;

  public function __construct(\UMA\DIC\Container $c, string $template, array $data = [])
  {
    $this->renderer = $c->get(PhpRenderer::class);
    $this->csrf = $c->get('csrf');
    $this->template = $template;
    $this->data = $data;
  }

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $csrf = [
      'csrf' => [
        'nameKey' => $nameKey,
        'valueKey' => $valueKey,
        'name' => $request->getAttribute($nameKey),
        'value' => $request->getAttribute($valueKey),
      ]
    ];
    return $this->renderer->render($response, $this->template . ".phtml", array_merge($this->data, $csrf));
  }
}
