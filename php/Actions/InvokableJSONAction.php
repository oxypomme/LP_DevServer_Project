<?php

namespace Crisis\Actions;

use Nyholm\Psr7;

abstract class InvokableJSONAction extends InvokableAction
{
  /**
   * Create a JSON response
   */
  protected function createResponse($data = null, $status = 200): Psr7\Response
  {
    if (is_null($data)) {
      $data = new \stdClass();
    }

    $body = Psr7\Stream::create(\Crisis\JSON::encode([
      'status' => $status,
      'payload' => $data
    ]) . PHP_EOL);

    return new Psr7\Response(
      $status,
      [
        'Content-Type' => 'application/json',
        'Content-Length' => $body->getSize()
      ],
      $body
    );
  }
}
