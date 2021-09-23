<?php

namespace Crisis\Actions;

use Nyholm\Psr7;
use function json_encode;

abstract class InvokableJSONAction extends InvokableAction
{
  /**
   * json_encode with base flags already defined
   * 
   * Base flags : `JSON_UNESCAPED_SLASHES`, `JSON_UNESCAPED_UNICODE` and `JSON_NUMERIC_CHECK`
   * 
   * @param mixed $value
   * @param int $f Additional flags
   * @return null|string The JSON or null if an error happened
   */
  protected function _json_encode($value, int $f = 0): ?string
  {
    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | $f;
    $res = json_encode($value, $flags);
    if (!$res) {
      return null;
    }
    return $res;
  }

  /**
   * Create a JSON response
   */
  protected function createResponse($data): Psr7\Response
  {
    $body = Psr7\Stream::create($this->_json_encode($data) . PHP_EOL);

    return new Psr7\Response(
      200,
      [
        'Content-Type' => 'application/json',
        'Content-Length' => $body->getSize()
      ],
      $body
    );
  }
}
