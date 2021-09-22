<?php

namespace Crisis\Actions\Users;

use Crisis\Models\User;
use Crisis\Actions\InvokableEMAction;
use Nyholm\Psr7;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use function json_encode;

class GetUser extends InvokableEMAction
{
    public function handle(Request $request, Response $response, array $args): Response
    {
        /** @var User $user */
        $user = $this->em
            ->getRepository(User::class)
            ->find($args['id']);

        $body = Psr7\Stream::create(json_encode($user, JSON_PRETTY_PRINT) . PHP_EOL);

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
